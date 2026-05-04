@csrf

@if ($question->exists)
    @method('PUT')
@endif

<label for="type">Question type</label>
<select id="type" name="type" required>
    @foreach ($types as $type)
        <option value="{{ $type->value }}" @selected(old('type', $question->type?->value) === $type->value)>{{ $type->value }}</option>
    @endforeach
</select>

<label for="difficulty_level">Difficulty</label>
<select id="difficulty_level" name="difficulty_level" required>
    @foreach (['EASY', 'MEDIUM', 'HARD'] as $difficulty)
        <option value="{{ $difficulty }}" @selected(old('difficulty_level', $question->difficulty_level) === $difficulty)>{{ $difficulty }}</option>
    @endforeach
</select>

<label for="question_text">Question text</label>
<textarea id="question_text" name="question_text" required>{{ old('question_text', $question->question_text) }}</textarea>

<label for="options_text">MCQ options, one per line</label>
<textarea id="options_text" name="options_text">{{ old('options_text', is_array($question->options) ? implode("\n", $question->options) : '') }}</textarea>

<label for="correct_answer">Correct answer or scoring reference</label>
<textarea id="correct_answer" name="correct_answer">{{ old('correct_answer', $question->correct_answer) }}</textarea>

<label for="points">Points</label>
<input id="points" name="points" type="number" min="0.01" step="0.01" value="{{ old('points', $question->points) }}" required>

<label>
    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $question->is_active ?? true)) style="display:inline;width:auto;">
    Active for future attempts
</label>

<button type="submit">Save question</button>
