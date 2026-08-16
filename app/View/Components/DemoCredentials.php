<?php

declare(strict_types=1);

namespace App\View\Components;

use App\Support\DemoCredentials\DemoCredential;
use App\Support\DemoCredentials\DemoCredentialsRepository;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

/**
 * Renders the demo-credentials block at the bottom of each auth login page
 * when `APP_DEMO=true`. The component degrades to an empty render in three
 * cases so it can be dropped into every login view unconditionally:
 *
 *   1. Demo mode is off (`config('app.demo')` is falsy).
 *   2. Demo mode is on but the seeders haven't run yet (no accounts found
 *      for this portal).
 *   3. The caller passes an unsupported portal value.
 *
 * Props:
 *   - portal  : 'user' | 'merchant' | 'agent'  (required)
 *   - formId  : DOM id of the form to autofill (optional). When omitted,
 *               the component falls back to the conventional ids the auth
 *               blades already render (`login`, `password`).
 */
class DemoCredentials extends Component
{
    /** @var Collection<int, DemoCredential> */
    public Collection $credentials;

    public bool $enabled;

    public function __construct(
        public string $portal,
        public ?string $formId = null,
        DemoCredentialsRepository $repository = new DemoCredentialsRepository,
    ) {
        $this->credentials = $repository->forPortal($portal);
        $this->enabled     = $repository->isEnabled() && $this->credentials->isNotEmpty();
    }

    public function render(): View|Closure|string
    {
        return view('components.demo-credentials');
    }
}
