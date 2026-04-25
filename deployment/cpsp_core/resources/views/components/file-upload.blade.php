@props(['name' => 'files[]', 'label' => 'Upload Files', 'multiple' => false, 'required' => false])

<div class="pt-2" x-data="fileUploadComponent('{{ $name }}', {{ $multiple ? 'true' : 'false' }})">
    @if($label)
        <label class="block text-sm font-semibold text-slate-700 mb-2">{{ $label }} @if($required)<span class="text-rose-500">*</span>@endif</label>
    @endif
    
    <label for="{{ $name }}" class="flex flex-col items-center justify-center w-full py-8 border-2 border-slate-300 border-dashed rounded-xl cursor-pointer bg-slate-50 hover:bg-slate-100 hover:border-slate-400 transition-all group relative"
        @dragover.prevent="dragover = true"
        @dragleave.prevent="dragover = false"
        @drop.prevent="drop($event)"
        :class="dragover ? 'border-indigo-500 bg-indigo-50' : 'border-slate-300 bg-slate-50'">
        <div class="flex flex-col items-center justify-center relative z-10 text-center px-4">
            <div class="bg-white text-slate-600 p-3 rounded-xl mb-3 border border-slate-200 shadow-sm group-hover:scale-105 transition-transform">
                <i data-lucide="upload-cloud" class="w-6 h-6"></i>
            </div>
            <p class="text-sm text-slate-700 mb-1"><span class="font-semibold text-indigo-600">Browse files</span> or drag and drop</p>
            <p class="text-xs text-slate-500">Supports PDF, JPG, PNG up to 10MB</p>
        </div>
        <input id="{{ $name }}" name="{{ $name }}" type="file" {{ $multiple ? 'multiple' : '' }} {{ $required ? 'required' : '' }} class="hidden" @change="handleFiles" x-ref="fileInput" />
    </label>

    <!-- Selected Files Preview -->
    <template x-if="files.length > 0">
        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
            <template x-for="(file, index) in files" :key="index">
                <div class="flex items-center justify-between p-3 bg-white border border-slate-200 rounded-xl shadow-sm">
                    <div class="flex items-center gap-3 truncate pr-4">
                        <div class="p-2 bg-indigo-100 text-indigo-600 rounded-lg shrink-0">
                            <i data-lucide="file" class="w-4 h-4"></i>
                        </div>
                        <div class="truncate">
                            <p class="text-xs font-semibold text-slate-900 truncate" x-text="file.name"></p>
                            <p class="text-[10px] text-slate-500" x-text="(file.size / 1024 / 1024).toFixed(2) + ' MB'"></p>
                        </div>
                    </div>
                    <button type="button" @click.prevent="removeFile(index)" class="shrink-0 p-1.5 text-rose-500 hover:bg-rose-50 rounded-lg transition-colors" title="Remove File">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
            </template>
        </div>
    </template>
</div>

@once
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('fileUploadComponent', (inputName, isMultiple) => ({
            dragover: false,
            files: [],
            isMultiple: isMultiple,
            
            handleFiles(event) {
                const newFiles = Array.from(event.target.files);
                this.addFiles(newFiles);
            },
            
            drop(event) {
                this.dragover = false;
                const newFiles = Array.from(event.dataTransfer.files);
                this.addFiles(newFiles);
                this.syncToInput();
            },
            
            addFiles(newFiles) {
                if (!this.isMultiple) {
                    this.files = newFiles.slice(0, 1);
                } else {
                    const existingNames = this.files.map(f => f.name);
                    const filtered = newFiles.filter(f => !existingNames.includes(f.name));
                    this.files = [...this.files, ...filtered];
                }
                
                this.syncToInput();
                
                this.$nextTick(() => {
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                });
            },
            
            removeFile(index) {
                this.files.splice(index, 1);
                this.syncToInput();
            },
            
            syncToInput() {
                const dataTransfer = new DataTransfer();
                this.files.forEach(f => dataTransfer.items.add(f));
                this.$refs.fileInput.files = dataTransfer.files;
            }
        }));
    });
</script>
@endonce
