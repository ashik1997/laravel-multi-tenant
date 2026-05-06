@csrf

<div class="grid grid-cols-1 gap-6 md:grid-cols-2">
    <div>
        <x-input-label for="name" :value="__('Name')" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $tenant->name ?? '')" required autofocus />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    <div>
        <x-input-label for="email" :value="__('Email')" />
        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $tenant->email ?? '')" required />
        <x-input-error class="mt-2" :messages="$errors->get('email')" />
    </div>

    <div>
        <x-input-label for="password" :value="$tenant ?? false ? __('Password') : __('Password')" />
        <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" :required="! isset($tenant)" />
        <x-input-error class="mt-2" :messages="$errors->get('password')" />
        @isset($tenant)
            <p class="mt-1 text-xs text-gray-500">Leave blank to keep the current password.</p>
        @endisset
    </div>

    <div>
        <x-input-label for="domain" :value="__('Domain')" />
        <x-text-input id="domain" name="domain" type="text" class="mt-1 block w-full" :value="old('domain', $tenant->domain ?? '')" placeholder="main.localhost" />
        <x-input-error class="mt-2" :messages="$errors->get('domain')" />
    </div>

    <div>
        <x-input-label for="database" :value="__('Database')" />
        <x-text-input id="database" name="database" type="text" class="mt-1 block w-full" :value="old('database', $tenant->database ?? '')" placeholder="tenant_main" />
        <x-input-error class="mt-2" :messages="$errors->get('database')" />
    </div>

    <div>
        <x-input-label for="driver" :value="__('Driver')" />
        <x-text-input id="driver" name="driver" type="text" class="mt-1 block w-full" :value="old('driver', $tenant->driver ?? 'mysql')" required />
        <x-input-error class="mt-2" :messages="$errors->get('driver')" />
    </div>

    <div>
        <x-input-label for="host" :value="__('Host')" />
        <x-text-input id="host" name="host" type="text" class="mt-1 block w-full" :value="old('host', $tenant->host ?? '127.0.0.1')" required />
        <x-input-error class="mt-2" :messages="$errors->get('host')" />
    </div>

    <div>
        <x-input-label for="port" :value="__('Port')" />
        <x-text-input id="port" name="port" type="text" class="mt-1 block w-full" :value="old('port', $tenant->port ?? '3306')" required />
        <x-input-error class="mt-2" :messages="$errors->get('port')" />
    </div>

    <div>
        <x-input-label for="dbusername" :value="__('DB Username')" />
        <x-text-input id="dbusername" name="dbusername" type="text" class="mt-1 block w-full" :value="old('dbusername', $tenant->dbusername ?? 'root')" required />
        <x-input-error class="mt-2" :messages="$errors->get('dbusername')" />
    </div>

    <div>
        <x-input-label for="dbpassword" :value="__('DB Password')" />
        <x-text-input id="dbpassword" name="dbpassword" type="text" class="mt-1 block w-full" :value="old('dbpassword', $tenant->dbpassword ?? '')" />
        <x-input-error class="mt-2" :messages="$errors->get('dbpassword')" />
    </div>

    <div>
        <x-input-label for="charset" :value="__('Charset')" />
        <x-text-input id="charset" name="charset" type="text" class="mt-1 block w-full" :value="old('charset', $tenant->charset ?? 'utf8mb4')" required />
        <x-input-error class="mt-2" :messages="$errors->get('charset')" />
    </div>

    <div>
        <x-input-label for="collation" :value="__('Collation')" />
        <x-text-input id="collation" name="collation" type="text" class="mt-1 block w-full" :value="old('collation', $tenant->collation ?? 'utf8mb4_unicode_ci')" required />
        <x-input-error class="mt-2" :messages="$errors->get('collation')" />
    </div>
</div>

<div class="mt-6 flex items-center justify-end gap-3">
    <a href="{{ route('tenants.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
    <x-primary-button>{{ $buttonText }}</x-primary-button>
</div>
