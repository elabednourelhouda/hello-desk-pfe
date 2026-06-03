@php
    $prospectForNeed = $prospect ?? null;
@endphp

{{-- Section: Besoin initial --}}
<div class="mb-8">
    <div class="mb-4">
        <h2 class="text-lg font-bold text-gray-900">
            Besoin initial
        </h2>
        <p class="mt-1 text-sm text-gray-500">
            Informations sur le besoin exprimé lors du premier contact avec le prospect.
        </p>
    </div>

    <div class="grid gap-5 md:grid-cols-2">
        <div>
            <label class="mb-2 block text-sm font-semibold text-gray-700">
                Campus souhaité
            </label>
            <select name="preferred_campus_id"
                    class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                <option value="">Non précisé</option>
                @foreach($campuses as $campus)
                    <option value="{{ $campus->id }}"
                        @selected(old('preferred_campus_id', optional($prospectForNeed)->preferred_campus_id) == $campus->id)>
                        {{ $campus->name }}
                    </option>
                @endforeach
            </select>

            @error('preferred_campus_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="mb-2 block text-sm font-semibold text-gray-700">
                Type d’espace recherché
            </label>
            <select name="preferred_space_type_id"
                    class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                <option value="">Non précisé</option>
                @foreach($spaceTypes as $type)
                    <option value="{{ $type->id }}"
                        @selected(old('preferred_space_type_id', optional($prospectForNeed)->preferred_space_type_id) == $type->id)>
                        {{ $type->name }}
                    </option>
                @endforeach
            </select>

            @error('preferred_space_type_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="mb-2 block text-sm font-semibold text-gray-700">
                Nombre de personnes / postes
            </label>
            <input type="number"
                   min="1"
                   name="people_count"
                   value="{{ old('people_count', optional($prospectForNeed)->people_count) }}"
                   placeholder="Ex: 2"
                   class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">

            @error('people_count')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="mb-2 block text-sm font-semibold text-gray-700">
                Budget approximatif
            </label>
            <input type="number"
                   step="0.01"
                   min="0"
                   name="budget"
                   value="{{ old('budget', optional($prospectForNeed)->budget) }}"
                   placeholder="Ex: 3000"
                   class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">

            @error('budget')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="mb-2 block text-sm font-semibold text-gray-700">
                Date de début souhaitée
            </label>
            <input type="date"
                   name="desired_start_date"
                   value="{{ old('desired_start_date', optional(optional($prospectForNeed)->desired_start_date)->format('Y-m-d')) }}"
                   class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">

            @error('desired_start_date')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="mb-2 block text-sm font-semibold text-gray-700">
                Durée souhaitée
            </label>
            <select name="desired_rental_period"
                    class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                <option value="">Non précisée</option>
                <option value="hourly" @selected(old('desired_rental_period', optional($prospectForNeed)->desired_rental_period) === 'hourly')>
                    À l’heure
                </option>
                <option value="daily" @selected(old('desired_rental_period', optional($prospectForNeed)->desired_rental_period) === 'daily')>
                    À la journée
                </option>
                <option value="monthly" @selected(old('desired_rental_period', optional($prospectForNeed)->desired_rental_period) === 'monthly')>
                    Au mois
                </option>
                <option value="custom" @selected(old('desired_rental_period', optional($prospectForNeed)->desired_rental_period) === 'custom')>
                    Personnalisée
                </option>
            </select>

            @error('desired_rental_period')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="md:col-span-2">
            <label class="mb-2 block text-sm font-semibold text-gray-700">
                Source du prospect
            </label>
            <input type="text"
                   name="source"
                   value="{{ old('source', optional($prospectForNeed)->source) }}"
                   placeholder="Ex: Visite, appel, site web, recommandation..."
                   class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">

            @error('source')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="mt-5">
        <label class="mb-2 block text-sm font-semibold text-gray-700">
            Description du besoin
        </label>
        <textarea name="need"
                  rows="4"
                  placeholder="Ex: Cherche un bureau privé pour 2 personnes à partir du mois prochain..."
                  class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">{{ old('need', optional($prospectForNeed)->need) }}</textarea>

        @error('need')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- 
        FUTURE CRM IMPROVEMENT - Évolution du besoin

        For now, the interface only shows one clear "Besoin initial" section
        to avoid confusing the user.

        Later, if Hello Desk needs deeper CRM tracking, we can activate a hidden/future
        "évolution du besoin" logic where the commercial can track:
        - updated campus preference
        - updated space type
        - updated budget
        - updated desired start date
        - extra notes about the prospect's changing need

        Technical idea already prepared:
        - table: prospect_requests
        - model: ProspectRequest
        - relation: prospectRequests()
        - controllers/routes can be reused later

        This part is intentionally not displayed in the interface for now.
    --}}
</div>