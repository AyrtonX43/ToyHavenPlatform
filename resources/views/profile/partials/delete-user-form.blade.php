<section class="mb-4">
    <div class="card border-danger">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0">{{ __('Delete Account') }}</h5>
        </div>
        <div class="card-body">
            <p class="text-muted">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>

            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#confirmUserDeletionModal">
                {{ __('Delete Account') }}
            </button>
        </div>
    </div>

    <!-- Bootstrap Modal -->
    <div class="modal fade" id="confirmUserDeletionModal" tabindex="-1" aria-labelledby="confirmUserDeletionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmUserDeletionModalLabel">
                        {{ __('Are you sure you want to delete your account?') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" action="{{ route('profile.destroy') }}">
            @csrf
            @method('delete')
                    <div class="modal-body">
                        <p class="text-muted">
                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
            </p>

                        <div class="mb-3">
                            <label for="password" class="form-label">{{ __('Password') }}</label>
                            <input
                    id="password"
                    name="password"
                    type="password"
                                class="form-control {{ $errors->userDeletion->has('password') ? 'is-invalid' : '' }}"
                    placeholder="{{ __('Password') }}"
                                required
                            >
                            @if($errors->userDeletion->has('password'))
                                <div class="invalid-feedback">{{ $errors->userDeletion->first('password') }}</div>
                            @endif
                        </div>
            </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    {{ __('Cancel') }}
                        </button>
                        <button type="submit" class="btn btn-danger">
                    {{ __('Delete Account') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

@if($errors->userDeletion->isNotEmpty())
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var modalElement = document.getElementById('confirmUserDeletionModal');
        if (modalElement) {
            var modal = new bootstrap.Modal(modalElement);
            modal.show();
        }
    });
</script>
@endpush
@endif
