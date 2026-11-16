@extends('web.layouts.app')

@section('title', 'ASILOV Studio')

@section('content')
    <style>
        :root {
            --studio-bg: #090b12;
            --studio-panel: #111522;
            --studio-panel-2: #171d2b;
            --studio-border: rgba(212, 175, 55, .25);
            --studio-gold: #d4af37;
            --studio-gold-light: #f2d477;
            --studio-purple: #8b5cf6;
            --studio-text: #f5f5f5;
            --studio-muted: #9ca3af;
        }

        .studio-page {
            background: var(--studio-bg);
            color: var(--studio-text);
            min-height: 100vh;
            padding: 38px 15px 65px;
        }

        .studio-wrapper {
            max-width: 1050px;
            margin: auto;
        }

        .studio-heading {
            margin-bottom: 30px;
        }

        .studio-heading h1 {
            font-size: 30px;
            font-weight: 800;
            letter-spacing: -.5px;
            color: white;
        }

        .studio-heading p {
            color: var(--studio-muted);
            margin-top: 8px;
        }

        .studio-section {
            background: var(--studio-panel);
            border: 1px solid var(--studio-border);
            border-radius: 15px;
            padding: 24px;
            margin-bottom: 22px;
        }

        .studio-section-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 20px;
        }

        .studio-section-title h3 {
            font-size: 19px;
            font-weight: 700;
            margin: 0;
            color: white;
        }

        .studio-section-title p {
            color: var(--studio-muted);
            font-size: 13px;
            margin: 6px 0 0;
        }

        .studio-label {
            display: block;
            font-weight: 600;
            margin-bottom: 9px;
            color: #e5e7eb;
        }

        .studio-optional {
            color: var(--studio-muted);
            font-size: 12px;
            font-weight: 400;
            margin-left: 5px;
        }

        .studio-input,
        .studio-textarea,
        .studio-select {
            width: 100%;
            background: #090d17;
            border: 1px solid #30394b;
            border-radius: 9px;
            color: white;
            padding: 12px 14px;
            outline: none;
        }

        .studio-input:focus,
        .studio-textarea:focus,
        .studio-select:focus {
            border-color: var(--studio-gold);
            box-shadow: 0 0 0 3px rgba(212, 175, 55, .1);
        }

        .studio-textarea {
            min-height: 145px;
            resize: vertical;
        }

        .studio-input::placeholder,
        .studio-textarea::placeholder {
            color: #687386;
        }

        .studio-help {
            color: var(--studio-muted);
            font-size: 12px;
            margin-top: 7px;
        }

        .studio-btn {
            border: 0;
            border-radius: 8px;
            padding: 10px 16px;
            font-weight: 700;
            cursor: pointer;
            transition: .2s;
        }

        .studio-btn-gold {
            background: linear-gradient(135deg, var(--studio-gold), var(--studio-gold-light));
            color: #111;
        }

        .studio-btn-gold:hover {
            filter: brightness(1.08);
            transform: translateY(-1px);
        }

        .studio-btn-outline {
            color: var(--studio-gold-light);
            background: transparent;
            border: 1px solid var(--studio-gold);
        }

        .studio-btn-outline:hover {
            background: rgba(212, 175, 55, .1);
        }

        .studio-btn-remove {
            color: #fca5a5;
            background: rgba(239, 68, 68, .1);
            border: 1px solid rgba(239, 68, 68, .3);
            padding: 7px 11px;
            font-size: 12px;
        }

        .studio-card {
            background: var(--studio-panel-2);
            border: 1px solid #30394b;
            border-radius: 12px;
            padding: 18px;
            margin-bottom: 15px;
        }

        .studio-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 17px;
        }

        .studio-card-header strong {
            color: var(--studio-gold-light);
        }

        .studio-upload {
            display: block;
            width: 100%;
            border: 1px dashed #566078;
            border-radius: 9px;
            padding: 16px;
            background: #0b101c;
            color: var(--studio-muted);
            cursor: pointer;
        }

        .studio-upload:hover {
            border-color: var(--studio-gold);
        }

        .studio-upload input {
            width: 100%;
            margin-top: 8px;
            color: #cbd5e1;
        }

        .studio-preview {
            display: none;
            margin-top: 12px;
            max-width: 180px;
            max-height: 150px;
            object-fit: cover;
            border-radius: 9px;
            border: 1px solid var(--studio-gold);
        }

        .voice-panel {
            background: #0b101c;
            border: 1px solid #30394b;
            border-radius: 10px;
            padding: 15px;
            margin-top: 15px;
        }

        .voice-list {
            max-height: 310px;
            overflow-y: auto;
            margin-top: 12px;
        }

        .voice-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            border: 1px solid #30394b;
            border-radius: 9px;
            margin-bottom: 8px;
            cursor: pointer;
            background: #111827;
        }

        .voice-item:hover,
        .voice-item.selected {
            border-color: var(--studio-purple);
            background: rgba(139, 92, 246, .1);
        }

        .voice-info {
            flex: 1;
            min-width: 0;
        }

        .voice-info strong {
            display: block;
            color: white;
            font-size: 14px;
        }

        .voice-info small {
            color: var(--studio-muted);
            font-size: 12px;
        }

        .voice-play {
            background: rgba(139, 92, 246, .18);
            color: #c4b5fd;
            border: 1px solid rgba(139, 92, 246, .4);
            border-radius: 7px;
            padding: 7px 10px;
            cursor: pointer;
        }

        .voice-select {
            background: var(--studio-purple);
            color: white;
            border: 0;
            border-radius: 7px;
            padding: 7px 11px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
        }

        .voice-item audio {
            display: none;
        }

        .voice-item {
            flex-wrap: wrap;
        }

        .voice-info small {
            display: block;
            line-height: 1.45;
        }

        @media(max-width: 480px) {
            .voice-item {
                gap: 8px;
                padding: 10px;
            }

            .voice-item>div:last-child {
                width: 100%;
                justify-content: flex-end;
            }
        }

        .studio-divider {
            border-top: 1px solid #30394b;
            margin: 18px 0;
        }

        .format-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
        }

        .format-option {
            position: relative;
        }

        .format-option input {
            position: absolute;
            opacity: 0;
        }

        .format-label {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 10px;
            background: #0b101c;
            border: 1px solid #30394b;
            border-radius: 10px;
            padding: 18px 8px;
            cursor: pointer;
            min-height: 125px;
        }

        .format-option input:checked+.format-label {
            border-color: var(--studio-gold);
            background: rgba(212, 175, 55, .08);
            box-shadow: 0 0 0 1px var(--studio-gold);
        }

        .format-shape {
            border: 2px solid var(--studio-gold);
            border-radius: 3px;
        }

        .shape-horizontal {
            width: 52px;
            height: 30px;
        }

        .shape-vertical {
            width: 25px;
            height: 43px;
        }

        .shape-square {
            width: 37px;
            height: 37px;
        }

        .shape-cinema {
            width: 60px;
            height: 25px;
        }

        .format-label strong {
            color: white;
            font-size: 13px;
        }

        .format-label small {
            color: var(--studio-muted);
        }

        .studio-submit-area {
            text-align: center;
            padding-top: 10px;
        }

        .studio-submit {
            width: 100%;
            max-width: 420px;
            padding: 16px 25px;
            font-size: 16px;
        }

        .studio-error {
            color: #fca5a5;
            font-size: 13px;
            margin-top: 6px;
        }

        .studio-alert {
            background: rgba(139, 92, 246, .1);
            border: 1px solid rgba(139, 92, 246, .4);
            border-radius: 9px;
            color: #ddd6fe;
            padding: 13px 15px;
            font-size: 13px;
            margin-bottom: 15px;
        }

        @media(max-width: 700px) {
            .studio-page {
                padding: 25px 12px 45px;
            }

            .studio-section {
                padding: 17px;
            }

            .studio-heading h1 {
                font-size: 25px;
            }

            .format-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .studio-section-title {
                align-items: flex-start;
            }
        }
    </style>

    <div class="studio-page">
        <div class="studio-wrapper">

            <div class="studio-heading">
                <h1>ASILOV STUDIO</h1>
                <p>Fikrini, karakterlerini ve dünyanı belirle. Video prodüksiyon talebini oluştur.</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Formda bazı hatalar var:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('studio.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- REVİZE TALEBİ --}}
                <section class="studio-section">
                    <div class="studio-section-title">
                        <div>
                            <h3>↻ Revize Talebi</h3>
                            <p>Elinde mevcut bir video varsa bağlantısını ve istediğin değişiklikleri paylaşabilirsin.</p>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="studio-label">
                            Mevcut Video Bağlantısı
                            <span class="studio-optional">İsteğe bağlı</span>
                        </label>
                        <input type="url" name="initial_revision_video_url" class="studio-input"
                            value="{{ old('initial_revision_video_url') }}" placeholder="https://...">
                    </div>

                    <div>
                        <label class="studio-label">
                            Revize Açıklaması
                            <span class="studio-optional">İsteğe bağlı</span>
                        </label>
                        <textarea name="initial_revision_description" class="studio-textarea"
                            placeholder="Videoda yapılmasını istediğin değişiklikleri açıklayabilirsin...">{{ old('initial_revision_description') }}</textarea>
                    </div>
                </section>

                {{-- SENARYO --}}
                <section class="studio-section">
                    <div class="studio-section-title">
                        <div>
                            <h3>✎ Senaryo</h3>
                            <p>Videonun hikâyesini ve sahnelerini anlat.</p>
                        </div>
                    </div>

                    <textarea name="scenario" class="studio-textarea"
                        placeholder="Senaryonu buraya yazabilirsin. Bu alan isteğe bağlıdır...">{{ old('scenario') }}</textarea>
                </section>

                {{-- KARAKTERLER --}}
                <section class="studio-section">
                    <div class="studio-section-title">
                        <div>
                            <h3>♙ Karakterler</h3>
                            <p>Videoda yer almasını istediğin karakterleri ekleyebilirsin.</p>
                        </div>

                        <button type="button" class="studio-btn studio-btn-outline" id="addCharacter">
                            + Karakter Ekle
                        </button>
                    </div>

                    <div id="charactersContainer"></div>
                </section>

                {{-- ORTAMLAR --}}
                <section class="studio-section">
                    <div class="studio-section-title">
                        <div>
                            <h3>▧ Ortamlar</h3>
                            <p>Videoda kullanılacak mekânları ve ortam görsellerini ekleyebilirsin.</p>
                        </div>

                        <button type="button" class="studio-btn studio-btn-outline" id="addEnvironment">
                            + Ortam Ekle
                        </button>
                    </div>

                    <div id="environmentsContainer"></div>
                </section>

                {{-- VİDEO FORMATI --}}
                <section class="studio-section">
                    <div class="studio-section-title">
                        <div>
                            <h3>▣ Video Formatı <span style="color:#ef4444">*</span></h3>
                            <p>Videonun yayınlanacağı ekran formatını seç.</p>
                        </div>
                    </div>

                    <div class="format-grid">
                        <label class="format-option">
                            <input type="radio" name="format" value="16:9"
                                {{ old('format') === '16:9' ? 'checked' : '' }} required>
                            <span class="format-label">
                                <span class="format-shape shape-horizontal"></span>
                                <strong>Yatay</strong>
                                <small>16:9</small>
                            </span>
                        </label>

                        <label class="format-option">
                            <input type="radio" name="format" value="9:16"
                                {{ old('format') === '9:16' ? 'checked' : '' }}>
                            <span class="format-label">
                                <span class="format-shape shape-vertical"></span>
                                <strong>Dikey</strong>
                                <small>9:16</small>
                            </span>
                        </label>

                        <label class="format-option">
                            <input type="radio" name="format" value="1:1"
                                {{ old('format') === '1:1' ? 'checked' : '' }}>
                            <span class="format-label">
                                <span class="format-shape shape-square"></span>
                                <strong>Kare</strong>
                                <small>1:1</small>
                            </span>
                        </label>

                        <label class="format-option">
                            <input type="radio" name="format" value="21:9"
                                {{ old('format') === '21:9' ? 'checked' : '' }}>
                            <span class="format-label">
                                <span class="format-shape shape-cinema"></span>
                                <strong>Sinematik</strong>
                                <small>21:9</small>
                            </span>
                        </label>
                    </div>

                    @error('format')
                        <div class="studio-error">{{ $message }}</div>
                    @enderror
                </section>

                <div class="studio-submit-area">
                    <button type="submit" class="studio-btn studio-btn-gold studio-submit">
                        Video Talebi Oluştur
                    </button>
                    <p class="studio-help mt-3">Talebin oluşturulduktan sonra Studio panelinde görüntülenebilir.</p>
                </div>

            </form>
        </div>
    </div>

    {{-- KARAKTER VE ORTAM ŞABLONLARI --}}
    <template id="characterTemplate">
        <div class="studio-card character-card">
            <div class="studio-card-header">
                <strong class="character-title">Karakter</strong>
                <button type="button" class="studio-btn studio-btn-remove remove-item">Kaldır</button>
            </div>

            <div class="mb-3">
                <label class="studio-label">Karakter Adı <span style="color:#ef4444">*</span></label>
                <input type="text" class="studio-input character-name" placeholder="Örn. Ana karakter" required>
            </div>

            <div class="mb-3">
                <label class="studio-label">Karakter Görseli <span class="studio-optional">İsteğe bağlı</span></label>
                <label class="studio-upload">
                    Karakter görseli seç
                    <input type="file" class="character-image" accept="image/jpeg,image/png,image/webp">
                </label>
                <img class="studio-preview character-preview" alt="Karakter önizlemesi">
                <button type="button" class="studio-btn studio-btn-remove image-remove"
                    style="display:none; margin-top:8px;">
                    × Görseli Kaldır
                </button>
            </div>

            <div class="studio-divider"></div>

            <div>
                <label class="studio-label">Hazır Ses Seçimi <span class="studio-optional">İsteğe bağlı</span></label>
                <input type="hidden" class="voice-key">

                <div class="voice-panel">
                    <input type="search" class="studio-input voice-search" placeholder="Seslerde ara...">

                    <div class="voice-list" aria-live="polite"></div>

                    <div class="selected-voice studio-help">Henüz ses seçilmedi.</div>
                </div>
            </div>

            <div class="studio-divider"></div>

            <div>
                <label class="studio-label">Ses Klonla (AI) <span class="studio-optional">İsteğe bağlı</span></label>
                <label class="studio-upload">
                    Kendi ses dosyanı yükle en az 10 sn
                    <input type="file" class="custom-voice" accept=".mp3,.wav,.m4a,.aac,.ogg,audio/*">
                </label>
                <div class="studio-help custom-voice-name">Henüz ses dosyası seçilmedi.</div>
                <button type="button" class="studio-btn studio-btn-remove custom-voice-remove"
                    style="display:none; margin-top:8px;">
                    × Ses Dosyasını Kaldır
                </button>
                <div class="studio-help">Yüklediğin ses dosyası talebinle birlikte ekibimize iletilir.</div>
            </div>
        </div>
    </template>

    <template id="environmentTemplate">
        <div class="studio-card environment-card">
            <div class="studio-card-header">
                <strong class="environment-title">Ortam</strong>
                <button type="button" class="studio-btn studio-btn-remove remove-item">Kaldır</button>
            </div>

            <div class="mb-3">
                <label class="studio-label">Ortam Adı <span style="color:#ef4444">*</span></label>
                <input type="text" class="studio-input environment-name" placeholder="Örn. Orman, ev, sokak..."
                    required>
            </div>

            <div>
                <label class="studio-label">Ortam Görseli <span class="studio-optional">İsteğe bağlı</span></label>
                <label class="studio-upload">
                    Ortam görseli seç
                    <input type="file" class="environment-image" accept="image/jpeg,image/png,image/webp">
                </label>
                <img class="studio-preview environment-preview" alt="Ortam önizlemesi">
                <button type="button" class="studio-btn studio-btn-remove image-remove"
                    style="display:none; margin-top:8px;">
                    × Görseli Kaldır
                </button>
            </div>
        </div>
    </template>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const charactersContainer = document.getElementById('charactersContainer');
            const environmentsContainer = document.getElementById('environmentsContainer');

            // Sesler config/studio_voices.php dosyasından gelir.
            const studioVoices = @json(array_values($voices ?? []));
            let characterCount = 0;
            let environmentCount = 0;

            // Form alanlarının indekslerini ekrandaki sıraya göre günceller.
            // Böylece en son eklenen (en üstteki) kart ilk sırada kaydedilir.
            function reindexCharacters() {
                charactersContainer.querySelectorAll('.character-card').forEach((card, index) => {
                    card.querySelector('.character-name').name = `characters[${index}][name]`;
                    card.querySelector('.character-image').name = `characters[${index}][image]`;
                    card.querySelector('.voice-key').name = `characters[${index}][voice_key]`;
                    card.querySelector('.custom-voice').name = `characters[${index}][custom_voice]`;
                });
            }

            function reindexEnvironments() {
                environmentsContainer.querySelectorAll('.environment-card').forEach((card, index) => {
                    card.querySelector('.environment-name').name = `environments[${index}][name]`;
                    card.querySelector('.environment-image').name = `environments[${index}][image]`;
                });
            }

            function categoryLabel(category) {
                const labels = {
                    female: 'Kadın ses',
                    male: 'Erkek ses',
                    child: 'Çocuk sesi'
                };

                return labels[category] || 'Ses';
            }

            function stopOtherVoiceSamples(currentAudio = null) {
                document.querySelectorAll('.voice-sample-audio').forEach(audio => {
                    if (audio !== currentAudio) {
                        audio.pause();
                        audio.currentTime = 0;
                    }
                });
            }

            function renderVoiceList(card, searchTerm = '') {
                const list = card.querySelector('.voice-list');
                const query = searchTerm.trim().toLocaleLowerCase('tr-TR');
                const selectedKey = card.querySelector('.voice-key').value;

                list.replaceChildren();

                const filteredVoices = studioVoices.filter(voice => {
                    const searchable = [
                        voice.name,
                        voice.description,
                        categoryLabel(voice.category)
                    ].filter(Boolean).join(' ').toLocaleLowerCase('tr-TR');

                    return searchable.includes(query);
                });

                if (!filteredVoices.length) {
                    const empty = document.createElement('div');
                    empty.className = 'studio-alert';
                    empty.textContent = studioVoices.length ?
                        'Aramana uygun ses bulunamadı.' :
                        'Henüz ses tanımlanmamış. config/studio_voices.php dosyasını kontrol et.';
                    list.appendChild(empty);
                    return;
                }

                filteredVoices.forEach(voice => {
                    const item = document.createElement('div');
                    item.className = 'voice-item' + (selectedKey === voice.key ? ' selected' : '');

                    const info = document.createElement('div');
                    info.className = 'voice-info';

                    const name = document.createElement('strong');
                    name.textContent = voice.name || voice.key;

                    const description = document.createElement('small');
                    description.textContent = (voice.description || categoryLabel(voice.category)) +
                        ' · ' + categoryLabel(voice.category);

                    info.append(name, description);

                    const actions = document.createElement('div');
                    actions.style.display = 'flex';
                    actions.style.alignItems = 'center';
                    actions.style.gap = '7px';

                    if (voice.sample_url) {
                        const play = document.createElement('button');
                        play.type = 'button';
                        play.className = 'voice-play';
                        play.textContent = '▶ Dinle';
                        play.setAttribute('aria-label', (voice.name || voice.key) + ' ses örneğini dinle');

                        const audio = document.createElement('audio');
                        audio.className = 'voice-sample-audio';
                        audio.preload = 'none';
                        audio.src = voice.sample_url;

                        play.addEventListener('click', async event => {
                            event.stopPropagation();

                            if (audio.paused) {
                                stopOtherVoiceSamples(audio);
                                try {
                                    await audio.play();
                                    play.textContent = 'Ⅱ Durdur';
                                } catch (error) {
                                    play.textContent = '▶ Dinle';
                                    alert(
                                        'Ses örneği oynatılamadı. CDN bağlantısını kontrol et.'
                                    );
                                }
                            } else {
                                audio.pause();
                                audio.currentTime = 0;
                                play.textContent = '▶ Dinle';
                            }
                        });

                        audio.addEventListener('ended', () => {
                            play.textContent = '▶ Dinle';
                        });
                        audio.addEventListener('pause', () => {
                            if (audio.currentTime === 0) play.textContent = '▶ Dinle';
                        });

                        actions.append(play, audio);
                    }

                    const select = document.createElement('button');
                    select.type = 'button';
                    select.className = 'voice-select';
                    select.textContent = selectedKey === voice.key ? 'Seçildi' : 'Seç';
                    select.addEventListener('click', event => {
                        event.stopPropagation();
                        chooseVoice(card, voice);
                    });

                    actions.appendChild(select);
                    item.append(info, actions);

                    item.addEventListener('click', () => chooseVoice(card, voice));
                    list.appendChild(item);
                });
            }

            function chooseVoice(card, voice) {
                const voiceInput = card.querySelector('.voice-key');
                const customVoiceInput = card.querySelector('.custom-voice');
                const removeButton = card.querySelector('.custom-voice-remove');

                // Aynı sese tekrar tıklanırsa seçimi kaldır.
                if (voiceInput.value === voice.key) {
                    voiceInput.value = '';
                    card.querySelector('.selected-voice').textContent = 'Henüz ses seçilmedi.';
                    renderVoiceList(card, card.querySelector('.voice-search').value);
                    return;
                }

                voiceInput.value = voice.key;

                customVoiceInput.value = '';
                card.querySelector('.custom-voice-name').textContent = 'Henüz ses dosyası seçilmedi.';
                removeButton.style.display = 'none';

                card.querySelector('.selected-voice').textContent =
                    'Seçilen ses: ' + (voice.name || voice.key);

                renderVoiceList(card, card.querySelector('.voice-search').value);
            }

            function previewImage(input, preview) {
                const card = input.closest('.studio-card');
                const removeButton = card.querySelector('.image-remove');

                function clearImage() {
                    input.value = '';
                    preview.removeAttribute('src');
                    preview.style.display = 'none';
                    removeButton.style.display = 'none';
                }

                input.addEventListener('change', function() {
                    const file = this.files[0];

                    if (!file) {
                        clearImage();
                        return;
                    }

                    if (!file.type.startsWith('image/')) {
                        alert('Lütfen geçerli bir görsel dosyası seç.');
                        clearImage();
                        return;
                    }

                    preview.src = URL.createObjectURL(file);
                    preview.style.display = 'block';
                    removeButton.style.display = 'inline-block';
                });

                removeButton.addEventListener('click', clearImage);
            }

            function addCharacter() {
                const template = document.getElementById('characterTemplate');
                const card = template.content.firstElementChild.cloneNode(true);
                const index = characterCount++;

                card.querySelector('.character-title').textContent = 'Karakter ' + (index + 1);

                card.querySelector('.character-name').name = `characters[${index}][name]`;
                card.querySelector('.character-image').name = `characters[${index}][image]`;
                card.querySelector('.voice-key').name = `characters[${index}][voice_key]`;
                card.querySelector('.custom-voice').name = `characters[${index}][custom_voice]`;

                previewImage(
                    card.querySelector('.character-image'),
                    card.querySelector('.character-preview')
                );

                card.querySelector('.voice-search').addEventListener('input', function() {
                    renderVoiceList(card, this.value);
                });

                renderVoiceList(card);

                card.querySelector('.custom-voice').addEventListener('change', function() {
                    card.querySelector('.custom-voice-name').textContent =
                        this.files[0] ? this.files[0].name : 'Henüz ses dosyası seçilmedi.';

                    if (this.files[0]) {
                        card.querySelector('.voice-key').value = '';
                        card.querySelector('.selected-voice').textContent = 'Özel ses dosyası seçildi.';
                        renderVoiceList(card, card.querySelector('.voice-search').value);
                    }
                });

                card.querySelector('.remove-item').addEventListener('click', () => {
                    card.remove();
                    reindexCharacters();
                });

                // Yeni karakter her zaman listenin en üstüne eklenir.
                charactersContainer.prepend(card);
                reindexCharacters();
            }

            function addEnvironment() {
                const template = document.getElementById('environmentTemplate');
                const card = template.content.firstElementChild.cloneNode(true);
                const index = environmentCount++;

                card.querySelector('.environment-title').textContent = 'Ortam ' + (index + 1);
                card.querySelector('.environment-name').name = `environments[${index}][name]`;
                card.querySelector('.environment-image').name = `environments[${index}][image]`;

                previewImage(
                    card.querySelector('.environment-image'),
                    card.querySelector('.environment-preview')
                );

                card.querySelector('.remove-item').addEventListener('click', () => {
                    card.remove();
                    reindexEnvironments();
                });

                // Yeni ortam her zaman listenin en üstüne eklenir.
                environmentsContainer.prepend(card);
                reindexEnvironments();
            }

            document.getElementById('addCharacter').addEventListener('click', addCharacter);
            document.getElementById('addEnvironment').addEventListener('click', addEnvironment);

            // İlk açılışta bir boş karakter ve ortam kartı gösterilmez.
        });
    </script>
@endsection
