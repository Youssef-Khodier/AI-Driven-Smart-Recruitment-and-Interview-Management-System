<?php

namespace App\Core;

final class App
{
    public function __construct(
        private readonly Router $router,
        private readonly Request $request,
    ) {
    }

    public function run(): void
    {
        try {
            $response = $this->router->dispatch($this->request);
        } catch (RedirectException $exception) {
            $response = new Response('', $exception->status(), ['Location' => $exception->location()]);
        } catch (HttpException $exception) {
            if ($exception->status() === 302 && isset($exception->headers()['Location'])) {
                $response = new Response('', 302, $exception->headers());
            } else {
                $this->logException($exception);
                $response = Response::view('errors/' . $exception->status(), [
                    'title' => $exception->status() . ' Error',
                    'message' => $exception->getMessage(),
                ], $exception->status());
            }
        } catch (ValidationException $exception) {
            Session::flash('errors', $exception->errors());
            Session::flash('old', $this->request->body());
            $response = Response::redirect($this->request->referer() ?: url('login'));
        } catch (\Throwable $exception) {
            $this->logException($exception);
            $response = Response::view('errors/500', [
                'title' => '500 Error',
                'message' => 'Unexpected server error.',
            ], 500);
        }

        $response->send();
        Session::ageFlash();
    }

    private function logException(\Throwable $exception): void
    {
        error_log(sprintf(
            '[SRIM] %s: %s in %s:%d',
            $exception::class,
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        ));
    }
}
