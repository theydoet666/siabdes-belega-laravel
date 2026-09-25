<div class="w-full max-w-md">
    <!-- Card Utama Form Login -->
    <div class="bg-white rounded-2xl shadow-xl shadow-daun/5 border border-garis overflow-hidden">
        <!-- Header Card -->
        <div class="bg-daun-tua px-8 py-8 text-center text-white relative">
            <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-white/10 backdrop-blur-sm border border-white/20 flex items-center justify-center shadow-inner">
                <svg class="w-9 h-9 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold tracking-tight">SIABDES Belega</h1>
            <p class="text-xs text-emerald-200/90 mt-1">Sistem Absensi Rapat Kantor Desa Belega</p>
        </div>

        <!-- Form Konten -->
        <div class="p-8">
            <div class="mb-6 text-center">
                <h2 class="text-lg font-semibold text-tinta">Masuk ke Sistem</h2>
                <p class="text-xs text-tinta/60 mt-0.5">Masukkan kredensial akun operator atau administrator Anda</p>
            </div>

            <!-- Error Banner Global (jika ada error umum) -->
            @error('email')
                <div class="mb-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-start gap-2.5">
                    <svg class="w-4 h-4 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>{{ $message }}</span>
                </div>
            @enderror

            <form wire:submit="masuk" class="space-y-4">
                <!-- Input Email -->
                <div>
                    <label for="email" class="block text-xs font-semibold text-tinta mb-1.5">
                        Alamat Email
                    </label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-tinta/40">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                            </svg>
                        </div>
                        <input wire:model="email" 
                               type="email" 
                               id="email" 
                               name="email"
                               autocomplete="email"
                               required
                               placeholder="nama@belega.desa.id"
                               class="block w-full pl-10 pr-3.5 py-2.5 bg-kertas/50 border @error('email') border-rose-300 focus:border-rose-500 focus:ring-rose-500 @else border-garis focus:border-daun focus:ring-daun @enderror rounded-xl text-sm text-tinta placeholder-tinta/30 focus:outline-none focus:ring-1 transition" />
                    </div>
                </div>

                <!-- Input Password dengan Toggle Intip -->
                <div x-data="{ showPass: false }">
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-xs font-semibold text-tinta">
                            Kata Sandi
                        </label>
                    </div>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-tinta/40">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <input wire:model="password" 
                               :type="showPass ? 'text' : 'password'" 
                               id="password" 
                               name="password"
                               autocomplete="current-password"
                               required
                               placeholder="••••••••"
                               class="block w-full pl-10 pr-10 py-2.5 bg-kertas/50 border @error('password') border-rose-300 focus:border-rose-500 focus:ring-rose-500 @else border-garis focus:border-daun focus:ring-daun @enderror rounded-xl text-sm text-tinta placeholder-tinta/30 focus:outline-none focus:ring-1 transition" />
                        <button type="button" 
                                @click="showPass = !showPass" 
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-tinta/40 hover:text-tinta transition cursor-pointer"
                                tabindex="-1">
                            <svg x-show="!showPass" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg x-show="showPass" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Ingat Saya -->
                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input wire:model="remember" 
                               type="checkbox" 
                               class="w-4 h-4 rounded text-daun focus:ring-daun border-garis transition cursor-pointer" />
                        <span class="text-xs text-tinta/80">Ingat sesi saya</span>
                    </label>
                </div>

                <!-- Tombol Submit -->
                <div class="pt-2">
                    <button type="submit" 
                            wire:loading.attr="disabled"
                            class="w-full flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-daun hover:bg-daun-tua text-white font-semibold text-sm shadow-md shadow-daun/20 focus:outline-none focus:ring-2 focus:ring-daun focus:ring-offset-2 transition disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer">
                        <span wire:loading.remove>Masuk ke Akun</span>
                        <span wire:loading class="inline-flex items-center gap-2">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Memverifikasi...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
