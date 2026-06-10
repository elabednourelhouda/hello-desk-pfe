@php
    $client = $client ?? null;
    $selectedType = old('client_type', optional($client)->client_type ?? '');
@endphp

<div class="md:col-span-2">
    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
        <h2 class="text-base font-bold text-slate-900">Informations juridiques</h2>
        <p class="mt-1 text-sm text-slate-500">
            Choisissez le type de client pour afficher uniquement les champs nécessaires.
        </p>

        <div class="mt-5 grid gap-5 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">
                    Type de client <span class="text-red-500">*</span>
                </label>
                <select id="client_type"
                        name="client_type"
                        required
                        class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                    <option value="">Choisir le type</option>
                    <option value="physique" @selected($selectedType === 'physique')>
                        Personne physique
                    </option>
                    <option value="morale" @selected($selectedType === 'morale')>
                        Personne morale
                    </option>
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">
                    Email de facturation
                </label>
                <input type="email"
                       name="billing_email"
                       value="{{ old('billing_email', optional($client)->billing_email ?? '') }}"
                       placeholder="Ex: facturation@email.com"
                       class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
            </div>
        </div>
    </div>
</div>

<div data-client-type-section="physique" class="md:col-span-2 hidden">
    <div class="rounded-2xl border border-slate-200 bg-white p-5">
        <h2 class="text-base font-bold text-slate-900">Personne physique</h2>

        <div class="mt-5 grid gap-5 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">
                    Prénom <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       name="first_name"
                       data-required="true"
                       value="{{ old('first_name', optional($client)->first_name ?? '') }}"
                       class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">
                    Nom <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       name="last_name"
                       data-required="true"
                       value="{{ old('last_name', optional($client)->last_name ?? '') }}"
                       class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">
                    Type de pièce <span class="text-red-500">*</span>
                </label>
                <select name="identity_document_type"
                        data-required="true"
                        class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                    <option value="">Choisir</option>
                    <option value="cin" @selected(old('identity_document_type', optional($client)->identity_document_type ?? '') === 'cin')>CIN</option>
                    <option value="passport" @selected(old('identity_document_type', optional($client)->identity_document_type ?? '') === 'passport')>Passport</option>
                    <option value="carte_sejour" @selected(old('identity_document_type', optional($client)->identity_document_type ?? '') === 'carte_sejour')>Carte séjour</option>
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">
                    Numéro de pièce <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       name="identity_document_number"
                       data-required="true"
                       value="{{ old('identity_document_number', optional($client)->identity_document_number ?? '') }}"
                       placeholder="Ex: BK123456"
                       class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">
                    Nationalité
                </label>
                <input type="text"
                       name="nationality"
                       value="{{ old('nationality', optional($client)->nationality ?? '') }}"
                       placeholder="Ex: Marocaine"
                       class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">
                    Ville
                </label>
                <input type="text"
                       name="city"
                       value="{{ old('city', optional($client)->city ?? '') }}"
                       placeholder="Ex: Casablanca"
                       class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
            </div>

            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-semibold text-slate-700">
                    Adresse
                </label>
                <input type="text"
                       name="address"
                       value="{{ old('address', optional($client)->address ?? '') }}"
                       placeholder="Adresse du client"
                       class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
            </div>
        </div>
    </div>
</div>

<div data-client-type-section="morale" class="md:col-span-2 hidden">
    <div class="rounded-2xl border border-slate-200 bg-white p-5">
        <h2 class="text-base font-bold text-slate-900">Personne morale</h2>

        <div class="mt-5 grid gap-5 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">
                    Raison sociale <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       name="company_name"
                       data-required="true"
                       value="{{ old('company_name', optional($client)->company_name ?? '') }}"
                       placeholder="Ex: Atlas Consulting SARL"
                       class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">
                    Forme juridique <span class="text-red-500">*</span>
                </label>
                <select name="legal_form"
                        data-required="true"
                        class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                    <option value="">Choisir</option>
                    <option value="sarl" @selected(old('legal_form', optional($client)->legal_form ?? '') === 'sarl')>SARL</option>
                    <option value="sa" @selected(old('legal_form', optional($client)->legal_form ?? '') === 'sa')>SA</option>
                    <option value="snc" @selected(old('legal_form', optional($client)->legal_form ?? '') === 'snc')>SNC</option>
                    <option value="auto_entrepreneur" @selected(old('legal_form', optional($client)->legal_form ?? '') === 'auto_entrepreneur')>Auto-entrepreneur</option>
                    <option value="association" @selected(old('legal_form', optional($client)->legal_form ?? '') === 'association')>Association</option>
                    <option value="other" @selected(old('legal_form', optional($client)->legal_form ?? '') === 'other')>Autre</option>
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">
                    ICE <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       name="ice_number"
                       data-required="true"
                       value="{{ old('ice_number', optional($client)->ice_number ?? '') }}"
                       class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">
                    IF
                </label>
                <input type="text"
                       name="if_number"
                       value="{{ old('if_number', optional($client)->if_number ?? '') }}"
                       class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">
                    RC
                </label>
                <input type="text"
                       name="rc_number"
                       value="{{ old('rc_number', optional($client)->rc_number ?? '') }}"
                       class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">
                    Patente / Taxe professionnelle
                </label>
                <input type="text"
                       name="patente_number"
                       value="{{ old('patente_number', optional($client)->patente_number ?? '') }}"
                       class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">
                    CNSS
                </label>
                <input type="text"
                       name="cnss_number"
                       value="{{ old('cnss_number', optional($client)->cnss_number ?? '') }}"
                       class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
            </div>

            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-semibold text-slate-700">
                    Adresse du siège social
                </label>
                <input type="text"
                       name="headquarters_address"
                       value="{{ old('headquarters_address', optional($client)->headquarters_address ?? '') }}"
                       class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
            </div>

            <div class="md:col-span-2 mt-2 border-t border-slate-200 pt-5">
                <h3 class="text-sm font-bold text-slate-900">Représentant légal</h3>
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">
                    Nom complet du représentant <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       name="legal_representative_full_name"
                       data-required="true"
                       value="{{ old('legal_representative_full_name', optional($client)->legal_representative_full_name ?? '') }}"
                       class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">
                    Type de pièce du représentant <span class="text-red-500">*</span>
                </label>
                <select name="legal_representative_identity_document_type"
                        data-required="true"
                        class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                    <option value="">Choisir</option>
                    <option value="cin" @selected(old('legal_representative_identity_document_type', optional($client)->legal_representative_identity_document_type ?? '') === 'cin')>CIN</option>
                    <option value="passport" @selected(old('legal_representative_identity_document_type', optional($client)->legal_representative_identity_document_type ?? '') === 'passport')>Passport</option>
                    <option value="carte_sejour" @selected(old('legal_representative_identity_document_type', optional($client)->legal_representative_identity_document_type ?? '') === 'carte_sejour')>Carte séjour</option>
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">
                    Numéro de pièce du représentant <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       name="legal_representative_identity_document_number"
                       data-required="true"
                       value="{{ old('legal_representative_identity_document_number', optional($client)->legal_representative_identity_document_number ?? '') }}"
                       class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">
                    Téléphone du représentant
                </label>
                <input type="text"
                       name="legal_representative_phone"
                       value="{{ old('legal_representative_phone', optional($client)->legal_representative_phone ?? '') }}"
                       class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">
                    Email du représentant
                </label>
                <input type="email"
                       name="legal_representative_email"
                       value="{{ old('legal_representative_email', optional($client)->legal_representative_email ?? '') }}"
                       class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
            </div>
        </div>
    </div>
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const typeSelect = document.getElementById('client_type');
                const sections = document.querySelectorAll('[data-client-type-section]');

                function toggleClientTypeSections() {
                    const selectedType = typeSelect.value;

                    sections.forEach(function (section) {
                        const isActive = section.dataset.clientTypeSection === selectedType;

                        section.classList.toggle('hidden', !isActive);

                        section.querySelectorAll('input, select, textarea').forEach(function (field) {
                            field.disabled = !isActive;

                            if (field.dataset.required === 'true') {
                                field.required = isActive;
                            }
                        });
                    });
                }

                if (typeSelect) {
                    typeSelect.addEventListener('change', toggleClientTypeSections);
                    toggleClientTypeSections();
                }
            });
        </script>
    @endpush
@endonce