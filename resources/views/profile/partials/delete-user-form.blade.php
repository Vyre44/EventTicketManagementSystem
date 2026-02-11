{{-- Hesap silme formu - Geri dönüşü olmayan işlem - Bootstrap 5 --}}
<h6 class="text-danger mb-3">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <strong>Uyarı:</strong> Hesabınız kalıcı olarak silinecektir. Bu işlem geri alınamaz!
</h6>

<p class="text-muted small mb-4">
    Hesabınız silindikten sonra, tüm verileri ve kişisel bilgilerini kalıcı olarak kaybedeceksiniz. 
    Lütfen devam etmeden önce önemli verilerinizi indirin.
</p>

<div class="d-grid gap-2 d-md-flex justify-content-md-start">
    <button 
        type="button" 
        class="btn btn-danger" 
        data-bs-toggle="modal" 
        data-bs-target="#confirmUserDeletion"
    >
        <i class="bi bi-trash-fill"></i> Hesabı Sil
    </button>
</div>

{{-- Silme Onayı Modal --}}
<div class="modal fade" id="confirmUserDeletion" tabindex="-1" aria-labelledby="confirmUserDeletionLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="confirmUserDeletionLabel">
                    <i class="bi bi-exclamation-triangle"></i> Hesabınızı Silmek İstiyor musunuz?
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>

            <form method="post" action="{{ route('profile.destroy') }}">
                @csrf
                @method('DELETE')

                <div class="modal-body">
                    <div class="alert alert-danger mb-4">
                        <i class="bi bi-info-circle"></i>
                        <strong>Bu işlem geri alınamaz!</strong> Hesabınız ve tüm verileri kalıcı olarak silinecektir.
                    </div>

                    <p class="text-muted mb-3">
                        Devam etmek için lütfen şifrenizi girin:
                    </p>

                    <label for="password" class="form-label fw-semibold">
                        <i class="bi bi-lock-fill"></i> Şifre
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-control @error('password', 'userDeletion') is-invalid @enderror" 
                        placeholder="Şifrenizi girin"
                        autofocus
                    >
                    @error('password', 'userDeletion')
                        <div class="invalid-feedback d-block">
                            <i class="bi bi-exclamation-circle"></i> {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> İptal
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash-fill"></i> Hesabı Kalıcı Olarak Sil
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
