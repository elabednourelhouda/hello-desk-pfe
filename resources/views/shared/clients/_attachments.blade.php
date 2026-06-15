@php
    $routePrefix = request()->routeIs('admin.*') ? 'admin' : 'commercial';
    $mode = $mode ?? 'view';
    $canManage = $mode === 'manage';

    $documentTypeLabels = [
        'cin_recto' => 'CIN recto',
        'cin_verso' => 'CIN verso',
        'passeport' => 'Passeport',
        'carte_sejour' => 'Carte de séjour',
        'ice' => 'ICE',
        'rc' => 'Registre de commerce',
        'patente' => 'Patente / TP',
        'cnss' => 'CNSS',
        'contrat' => 'Contrat',
        'facture' => 'Facture',
        'autre' => 'Autre',
    ];
@endphp

<section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
    <div class="flex flex-col gap-2 md:flex-row md:items-start md:justify-between">
        <div>
            <h2 class="text-lg font-bold text-slate-900">Pièces jointes</h2>
            <p class="mt-1 text-sm text-slate-500">
                Documents du dossier client : CIN, contrats, justificatifs, PDF ou photos.
            </p>
        </div>

        <span class="inline-flex w-fit rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">
            {{ $client->attachments->count() }} fichier(s)
        </span>
    </div>

    @if($canManage)
        <form method="POST"
              action="{{ route($routePrefix . '.clients.attachments.store', $client) }}"
              enctype="multipart/form-data"
              class="mt-5 rounded-xl border border-slate-200 bg-slate-50 p-5">
            @csrf

            <h3 class="text-sm font-bold text-slate-900">
                Ajouter un document
            </h3>

            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">
                        Type de document <span class="text-red-500">*</span>
                    </label>

                    <select name="document_type"
                            class="h-12 w-full rounded-xl border border-slate-300 bg-white px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]"
                            required>
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

                    @error('document_type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">
                        Nom du document
                    </label>

                    <input type="text"
                           name="title"
                           value="{{ old('title') }}"
                           placeholder="Ex : CIN du représentant légal"
                           class="h-12 w-full rounded-xl border border-slate-300 bg-white px-4 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">

                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-semibold text-slate-700">
                        Description optionnelle
                    </label>

                    <textarea name="notes"
                              rows="2"
                              placeholder="Ex : Document reçu après signature du contrat..."
                              class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm shadow-sm focus:border-[#284625] focus:ring-[#284625]">{{ old('notes') }}</textarea>

                    @error('notes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-semibold text-slate-700">
                        Fichier <span class="text-red-500">*</span>
                    </label>

                    <input type="file"
                           name="file"
                           accept=".pdf,.jpg,.jpeg,.png,.webp"
                           class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm shadow-sm file:mr-4 file:rounded-lg file:border-0 file:bg-[#284625] file:px-4 file:py-2 file:text-sm file:font-bold file:text-white hover:file:bg-[#1f351d]"
                           required>

                    <p class="mt-2 text-xs text-slate-500">
                        Formats acceptés : PDF, JPG, PNG, WEBP. Taille maximale : 5 Mo.
                    </p>

                    @error('file')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-4 flex justify-end">
                <button type="submit"
                        class="inline-flex h-11 items-center justify-center rounded-xl bg-[#284625] px-5 text-sm font-bold text-white shadow-sm hover:bg-[#1f351d]">
                    Ajouter le document
                </button>
            </div>
        </form>
    @endif

    <div class="mt-5 space-y-3">
        @forelse($client->attachments as $attachment)
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-[#284625]/10 px-3 py-1 text-xs font-bold text-[#284625]">
                                {{ $documentTypeLabels[$attachment->document_type] ?? 'Document' }}
                            </span>

                            <span class="text-xs font-semibold text-slate-400">
                                {{ $attachment->created_at?->format('d/m/Y H:i') }}
                            </span>
                        </div>

                        <h3 class="mt-2 text-sm font-bold text-slate-900">
                            {{ $attachment->title ?: $attachment->original_name }}
                        </h3>

                        <p class="mt-1 text-xs text-slate-500">
                            {{ $attachment->original_name }}
                            @if($attachment->size)
                                — {{ number_format($attachment->size / 1024, 0) }} Ko
                            @endif
                        </p>

                        @if($attachment->uploader)
                            <p class="mt-1 text-xs text-slate-500">
                                Ajouté par {{ $attachment->uploader->name }}
                            </p>
                        @endif

                        @if($attachment->notes)
                            <p class="mt-3 text-sm leading-6 text-slate-700">
                                {{ $attachment->notes }}
                            </p>
                        @endif
                    </div>

                    <div class="flex flex-wrap gap-2 md:justify-end">
                        <a href="{{ route($routePrefix . '.clients.attachments.preview', $attachment) }}"
                           target="_blank"
                           class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-700 hover:bg-slate-100">
                            Ouvrir
                        </a>

                        <a href="{{ route($routePrefix . '.clients.attachments.download', $attachment) }}"
                           class="inline-flex h-10 items-center justify-center rounded-xl border border-blue-200 bg-blue-50 px-4 text-sm font-bold text-blue-700 hover:bg-blue-100">
                            Télécharger
                        </a>

                        @if($canManage)
                            <form method="POST"
                                  action="{{ route($routePrefix . '.clients.attachments.destroy', $attachment) }}"
                                  onsubmit="return confirm('Supprimer cette pièce jointe ?');">
                                @csrf
                                @method('DELETE')

                                <button type="submit"
                                        class="inline-flex h-10 items-center justify-center rounded-xl border border-red-200 bg-red-50 px-4 text-sm font-bold text-red-700 hover:bg-red-100">
                                    Supprimer
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">
                Aucune pièce jointe pour ce client.
            </div>
        @endforelse
    </div>
</section>