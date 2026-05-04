@csrf

@if ($requisition->exists)
    @method('PUT')
    <input type="hidden" name="last_seen_updated_at" value="{{ $requisition->updated_at?->toIso8601String() }}">
@endif

<label for="department_id">Department</label>
<select id="department_id" name="department_id" required>
    <option value="">Select a department</option>
    @foreach ($departments as $department)
        <option value="{{ $department->department_id }}" @selected(old('department_id', $requisition->department_id) == $department->department_id)>
            {{ $department->name }}
        </option>
    @endforeach
</select>

<label for="title">Title</label>
<input id="title" name="title" value="{{ old('title', $requisition->title) }}" required>

<label for="description">Description</label>
<textarea id="description" name="description" required style="display:block;width:100%;max-width:32rem;min-height:8rem;margin:.25rem 0 1rem;">{{ old('description', $requisition->description) }}</textarea>

<label for="requirements">Requirements</label>
<textarea id="requirements" name="requirements" required style="display:block;width:100%;max-width:32rem;min-height:8rem;margin:.25rem 0 1rem;">{{ old('requirements', $requisition->requirements) }}</textarea>

<button type="submit">Save requisition</button>
