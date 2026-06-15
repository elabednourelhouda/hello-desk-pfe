<section class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
    <div class="flex flex-col gap-1">
        <h2 class="text-lg font-bold text-slate-900">Pièce jointe initiale</h2>
        <p class="text-sm text-slate-500">
            Optionnel : ajoutez un premier document au dossier client. Vous pourrez ajouter d’autres fichiers depuis la page de modification.
        </p>
    </div>

    <div class="mt-5 rounded-xl border border-slate-200 bg-white p-5">
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">
                    Type de document
                </label>

                <select name="attachments[0][document_type]"
                        class="h-12 w-full rounded-xl border border-slate-300 bg-white px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
                    <option value="cin_recto">CIN recto</option>
                    <option value="cin_verso">CIN verso</option>
                    <option value="passeport">Passeport</option>
                    <option value="carte_sejour">Carte de séjour</option>
                    <option value="ice">ICE</option>
                    <option value="rc">Registre de commerce</option>
                    <option value="patente">Patente / TP</option>
                    <option value="cnss">CNSS</option>
                    <option value="contrat">Contrat</option>
                    <option value="facture">Facture</option>
                    <option value="autre">Autre</option>
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">
                    Nom du document
                </label>

                <input type="text"
                       name="attachments[0][title]"
                       value="{{ old('attachments.0.title') }}"
                       placeholder="Ex : CIN du client"
                       class="h-12 w-full rounded-xl border border-slate-300 bg-white px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">
            </div>

            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-semibold text-slate-700">
                    Description optionnelle
                </label>

                <textarea name="attachments[0][notes]"
                          rows="2"
                          placeholder="Ex : Document reçu lors de la création du dossier..."
                          class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">{{ old('attachments.0.notes') }}</textarea>
            </div>

            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-semibold text-slate-700">
                    Fichier
                </label>

                <input type="file"
                       name="attachments[0][file]"
                       accept=".pdf,.jpg,.jpeg,.png,.webp"
                       class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm shadow-sm file:mr-4 file:rounded-lg file:border-0 file:bg-[#284625] file:px-4 file:py-2 file:text-sm file:font-bold file:text-white hover:file:bg-[#1f351d]">

                <p class="mt-2 text-xs text-slate-500">
                    Formats acceptés : PDF, JPG, PNG, WEBP. Taille maximale : 5 Mo.
                </p>

                @error('attachments.0.file')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>
</section>