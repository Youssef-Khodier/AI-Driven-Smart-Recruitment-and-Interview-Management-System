<?php

namespace App\Services;

use App\Core\Database;
use App\Repositories\PostOfferAuditRepository;

/**
 * Generates versioned digital offer letters from templates.
 * Letters are stored as HTML snapshots linked to the offer and template version.
 */
final class OfferLetterTemplateService
{
    private const DEFAULT_TEMPLATE = <<<'HTML'
<div style="font-family: Arial, sans-serif; max-width: 700px; margin: 0 auto; padding: 40px;">
    <div style="text-align: center; border-bottom: 2px solid #2563eb; padding-bottom: 20px; margin-bottom: 30px;">
        <h1 style="color: #1e3a5f; margin: 0;">SRIM Corporation</h1>
        <p style="color: #6b7280; margin: 5px 0 0;">Official Offer Letter</p>
    </div>

    <p>Date: {{date}}</p>
    <p>Dear <strong>{{candidate_name}}</strong>,</p>

    <p>We are pleased to extend an offer of employment for the position of <strong>{{job_title}}</strong>
    in the <strong>{{department}}</strong> department.</p>

    <h3 style="color: #1e3a5f; border-bottom: 1px solid #e5e7eb; padding-bottom: 8px;">Compensation Details</h3>
    <table style="width: 100%; border-collapse: collapse; margin: 15px 0;">
        <tr><td style="padding: 8px; border-bottom: 1px solid #e5e7eb;">Employment Type</td>
            <td style="padding: 8px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">{{offer_type}}</td></tr>
        <tr><td style="padding: 8px; border-bottom: 1px solid #e5e7eb;">Base Salary (CTC)</td>
            <td style="padding: 8px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">{{ctc}}</td></tr>
        <tr><td style="padding: 8px; border-bottom: 1px solid #e5e7eb;">Annual Bonus</td>
            <td style="padding: 8px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">{{bonus}}</td></tr>
        <tr><td style="padding: 8px; border-bottom: 1px solid #e5e7eb;">Stock Options</td>
            <td style="padding: 8px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">{{stock_options}}</td></tr>
        <tr style="background: #f0f9ff;"><td style="padding: 8px; font-weight: bold;">Total Compensation</td>
            <td style="padding: 8px; font-weight: bold;">{{total_compensation}}</td></tr>
    </table>

    <p>This offer is valid until <strong>{{expiry_date}}</strong>. Please respond by that date.</p>

    <p>We look forward to welcoming you to the team!</p>

    <div style="margin-top: 40px;">
        <p>Sincerely,<br><strong>SRIM Human Resources</strong></p>
    </div>

    <div style="margin-top: 30px; padding-top: 15px; border-top: 1px solid #e5e7eb; font-size: 12px; color: #9ca3af;">
        <p>Offer ID: {{offer_id}} | Sequence: {{offer_sequence}} | Generated: {{generated_at}}</p>
    </div>
</div>
HTML;

    /**
     * Generate a digital offer letter from the offer data.
     *
     * @param array $offer The full offer row (with joins to application, job, candidate)
     * @param string|null $customTemplate Optional custom HTML template with placeholders
     * @return array ['html' => string, 'template_version' => string, 'generated_at' => string]
     */
    public function generate(array $offer, ?string $customTemplate = null): array
    {
        $template = $customTemplate ?? self::DEFAULT_TEMPLATE;
        $generatedAt = date('Y-m-d H:i:s');

        $placeholders = [
            '{{date}}' => date('F j, Y'),
            '{{candidate_name}}' => htmlspecialchars($offer['candidate_name'] ?? 'Candidate'),
            '{{job_title}}' => htmlspecialchars($offer['job_title'] ?? 'Position'),
            '{{department}}' => htmlspecialchars($offer['department_name'] ?? 'Department'),
            '{{offer_type}}' => htmlspecialchars($offer['offer_type'] ?? 'FULL_TIME'),
            '{{ctc}}' => number_format((float)($offer['ctc'] ?? 0), 2),
            '{{bonus}}' => number_format((float)($offer['bonus'] ?? 0), 2),
            '{{stock_options}}' => number_format((float)($offer['stock_options'] ?? 0), 2),
            '{{total_compensation}}' => number_format(
                (float)($offer['ctc'] ?? 0) + (float)($offer['bonus'] ?? 0) + (float)($offer['stock_options'] ?? 0),
                2
            ),
            '{{expiry_date}}' => date('F j, Y', strtotime($offer['expiry_date'] ?? 'now')),
            '{{offer_id}}' => (string)($offer['offer_id'] ?? ''),
            '{{offer_sequence}}' => (string)($offer['offer_sequence'] ?? '1'),
            '{{generated_at}}' => $generatedAt,
        ];

        $html = str_replace(array_keys($placeholders), array_values($placeholders), $template);

        // Determine template version based on whether the default or a custom template is used.
        $templateVersion = $customTemplate ? 'custom_' . substr(md5($customTemplate), 0, 8) : 'default_v1';

        return [
            'html' => $html,
            'template_version' => $templateVersion,
            'generated_at' => $generatedAt,
        ];
    }

    /**
     * Generate and persist the letter as a snapshot in the database.
     *
     * @param int $offerId The offer ID
     * @param array $offer The full offer data with joins
     * @param int $actorId The HR user generating the letter
     * @return int The insert ID of the letter snapshot
     */
    public function generateAndStore(int $offerId, array $offer, int $actorId): int
    {
        $result = $this->generate($offer);

        PostOfferAuditRepository::record((int)$offer['application_id'], $offerId, null, $actorId, 'OFFER_LETTER_GENERATED', [
            'template_version' => $result['template_version'],
            'generated_at' => $result['generated_at'],
            'letter_html' => $result['html'],
        ]);

        return (int)Database::pdo()->lastInsertId();
    }

    /**
     * Retrieve the latest generated letter for an offer.
     *
     * @param int $offerId
     * @return array|null ['html' => string, 'template_version' => string, 'generated_at' => string] or null
     */
    public function getLatestLetter(int $offerId): ?array
    {
        $record = Database::fetch(
            "SELECT changed_fields FROM post_offer_audit_records WHERE offer_id = ? AND action = 'OFFER_LETTER_GENERATED' ORDER BY created_at DESC, audit_id DESC LIMIT 1",
            [$offerId]
        );

        if (!$record || !$record['changed_fields']) {
            return null;
        }

        return json_decode($record['changed_fields'], true);
    }
}
