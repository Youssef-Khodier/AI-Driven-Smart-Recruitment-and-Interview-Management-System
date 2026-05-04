@csrf

@if ($assessment->exists)
    @method('PUT')
@endif

<label for="title">Title</label>
<input id="title" name="title" value="{{ old('title', $assessment->title) }}" required>

<label for="type">Type</label>
<select id="type" name="type" required>
    @foreach ($types as $type)
        <option value="{{ $type->value }}" @selected(old('type', $assessment->type?->value) === $type->value)>{{ $type->value }}</option>
    @endforeach
</select>

<label for="duration_minutes">Duration in minutes</label>
<input id="duration_minutes" name="duration_minutes" type="number" min="1" value="{{ old('duration_minutes', $assessment->duration_minutes) }}" required>

<label for="description">Instructions</label>
<textarea id="description" name="description">{{ old('description', $assessment->description) }}</textarea>

<label>
    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $assessment->is_active ?? true)) style="display:inline;width:auto;">
    Active for new candidate attempts
</label>

<button type="submit">Save assessment</button>
