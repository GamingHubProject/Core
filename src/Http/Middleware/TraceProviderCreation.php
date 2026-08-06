<?php

namespace Azuriom\Plugin\GamingHubCore\Http\Middleware;

use Azuriom\Plugin\GamingHubCore\Services\ProviderCreationTrace;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class TraceProviderCreation
{
    public function __construct(private readonly ProviderCreationTrace $trace)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->isProviderCreate($request)) {
            return $next($request);
        }

        $this->trace->request($request);

        try {
            $response = $next($request);
        } catch (ValidationException $exception) {
            $errors = $exception->errors();
            $this->trace->validationFailed($errors);

            // Azuriom's admin layout always renders session('error'), including
            // extension-overridden provider forms that omit an error summary.
            $messages = collect($errors)->flatten()->filter()->unique()->values();
            if ($messages->isNotEmpty() && $request->hasSession()) {
                $request->session()->flash('error', $messages->implode(' '));
            }

            throw $exception;
        } catch (Throwable $exception) {
            $this->trace->failed($exception, 'request_pipeline');

            throw $exception;
        }

        $this->trace->stage('response_returned', [
            'status' => $response->getStatusCode(),
            'redirect' => method_exists($response, 'getTargetUrl') ? $response->getTargetUrl() : null,
        ]);

        return $response;
    }

    private function isProviderCreate(Request $request): bool
    {
        if (! $request->isMethod('POST')) {
            return false;
        }

        $routeName = (string) $request->route()?->getName();

        return str_ends_with($routeName, '.providers.store');
    }
}
