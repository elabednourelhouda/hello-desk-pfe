@php
$prospectForIdentity = $prospect ?? null;
@endphp

<div>
    <label class="mb-2 block text-sm font-semibold text-gray-700">
        Origine du prospect
    </label>
    <div class="flex h-12 items-center gap-6">
        <label class="flex items-center gap-2 text-sm text-gray-700">
            <input type="radio" name="origin" value="local"
                @checked(old('origin', optional($prospectForIdentity)->origin) === 'local')
                class="h-4 w-4 text-[#284625] focus:ring-[#284625]">
            Local
        </label>
        <label class="flex items-center gap-2 text-sm text-gray-700">
            <input type="radio" name="origin" value="etranger"
                @checked(old('origin', optional($prospectForIdentity)->origin) === 'etranger')
                class="h-4 w-4 text-[#284625] focus:ring-[#284625]">
            Étranger
        </label>
    </div>
    @error('origin')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div>
    <label class="mb-2 block text-sm font-semibold text-gray-700">
        Type de client
    </label>
    <div class="flex h-12 items-center gap-6">
        <label class="flex items-center gap-2 text-sm text-gray-700">
            <input type="radio" name="customer_type" value="physique"
                @checked(old('customer_type', optional($prospectForIdentity)->customer_type) === 'physique')
                class="h-4 w-4 text-[#284625] focus:ring-[#284625]">
            Personne Physique
        </label>
        <label class="flex items-center gap-2 text-sm text-gray-700">
            <input type="radio" name="customer_type" value="morale"
                @checked(old('customer_type', optional($prospectForIdentity)->customer_type) === 'morale')
                class="h-4 w-4 text-[#284625] focus:ring-[#284625]">
            Personne Morale
        </label>
    </div>
    @error('customer_type')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div class="md:col-span-2">
    <label class="mb-2 block text-sm font-semibold text-gray-700">
        Secteur d’activité
    </label>
    <select name="activity_sector_id"
        class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
        <option value="">Non précisé</option>
        @foreach($activitySectors as $sector)
        <option value="{{ $sector->id }}"
            @selected(old('activity_sector_id', optional($prospectForIdentity)->activity_sector_id) == $sector->id)>
            {{ $sector->name }}
        </option>
        @endforeach
    </select>
    @error('activity_sector_id')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>