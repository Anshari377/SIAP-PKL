<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? array_merge($user->toArray(), [
                    'tipe_pendaftaran' => $user->tipe_pendaftaran,
                ]) : null,
                'roles' => $user ? $user->getRoleNames() : [],
                'instansi' => $this->resolveInstansi($user),
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }

    private function resolveInstansi($user): ?array
    {
        if (! $user) {
            return null;
        }

        $agency = $user->agency;

        if ($agency) {
            return [
                'id' => $agency->id,
                'nama' => $agency->name,
                'nama_singkat' => $agency->nama_singkat ?: $agency->name,
                'slug' => $agency->slug,
                'logo' => $agency->logo,
            ];
        }

        if ($user->instansi) {
            return [
                'id' => null,
                'nama' => $user->instansi,
                'nama_singkat' => $user->instansi,
                'slug' => null,
                'logo' => null,
            ];
        }

        return null;
    }
}
