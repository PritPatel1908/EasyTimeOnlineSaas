<style>
    body:has(.tenant-license-modal) .main-wrapper > *:not(.tenant-license-modal) {
        filter: blur(5px);
        pointer-events: none;
        user-select: none;
    }

    .tenant-license-modal {
        align-items: center;
        background: transparent;
        border: none;
        display: flex;
        inset: 0;
        justify-content: center;
        padding: 20px;
        position: fixed;
        z-index: 1090;
    }

    .tenant-license-modal::backdrop {
        background: rgba(15, 23, 42, .55);
    }

    .tenant-license-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 24px 70px rgba(15, 23, 42, .25);
        max-width: 520px;
        padding: 32px;
        width: 100%;
    }

    .tenant-license-card h2 { margin: 0 0 10px; }
    .tenant-license-card p { color: #64748b; line-height: 1.6; }
    .tenant-license-card textarea { min-height: 110px; resize: vertical; }
</style>

<dialog open class="tenant-license-modal" aria-labelledby="tenant-license-title">
    <div class="tenant-license-card">
        <div class="d-flex align-items-center mb-3">
            <span class="avatar avatar-md bg-danger-transparent me-3"><i class="ti ti-key"></i></span>
            <div>
                <h2 id="tenant-license-title" class="h4 mb-0">Licence required</h2>
                <small class="text-muted">Update your workspace licence to continue.</small>
            </div>
        </div>
        <p>Enter the licence key provided by your administrator. Your workspace will be unlocked after successful verification.</p>
        @if (session('license_success'))
            <div class="alert alert-success">{{ session('license_success') }}</div>
        @endif
        @error('license_key')
            <div class="alert alert-danger">{{ $message }}</div>
        @enderror
        <form method="POST" action="{{ url('/license') }}">
            @csrf
            <label class="form-label" for="tenant-license-key">Licence key</label>
            <textarea id="tenant-license-key" name="license_key" class="form-control" required autofocus>{{ old('license_key') }}</textarea>
            <button type="submit" class="btn btn-primary w-100 mt-3">Verify and update licence</button>
        </form>
    </div>
</dialog>
