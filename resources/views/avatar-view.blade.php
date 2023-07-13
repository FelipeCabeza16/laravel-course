<x-layout>
    <div class="container container--narrow py-md-5">
        <h2 class="text-center mb-3">Crear Avatar</h2>
        <form action="/manage-avatar" method="post" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <input type="file" name="avatar" required>
            @error('avatar')
                <p class="alert small text-danger shadow-sm">{{ $message }}</p>   
            @enderror
        </div>
        <button class="btn btn-primary">Guardar</button>
        </form>
    </div>
</x-layout>