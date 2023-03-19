<?php

declare(strict_types=1);

namespace Leeto\MoonShine\Http\Controllers;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Routing\Redirector;
use Leeto\MoonShine\Exceptions\ResourceException;
use Leeto\MoonShine\QueryTags\QueryTag;
use Leeto\MoonShine\Resources\Resource;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Throwable;

class MoonShineCrudController extends BaseController
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;

    public function __construct()
    {
        $this->middleware(HandlePrecognitiveRequests::class)
            ->only(['store', 'update']);
    }

    protected Resource $resource;

    public function formAction($id, $index): RedirectResponse
    {
        $item = $this->resource->getModel()
            ->newModelQuery()
            ->findOrFail($id);

        abort_if(! $action = $this->resource->formActions()[$index] ?? false, ResponseAlias::HTTP_NOT_FOUND);
        abort_if(! $this->resource->can('viewAny', $this->resource->getModel()), ResponseAlias::HTTP_FORBIDDEN);

        if (! $redirectRoute = $action->getRedirectTo()) {
            $redirectRoute = redirect($this->resource->route('index'));

            if (request()->has('relatable_mode')) {
                $redirectRoute = back();
            }
        }

        try {
            $action->callback($item);
        } catch (Throwable $e) {
            throw_if(! app()->isProduction(), $e);
            report($e);

            return $redirectRoute
                ->with('alert', trans('moonshine::ui.saved_error'));
        }

        return $redirectRoute
            ->with('alert', $action->message());
    }

    public function action($id, $index): RedirectResponse
    {
        $item = $this->resource->getModel()
            ->newModelQuery()
            ->findOrFail($id);

        abort_if(! $action = $this->resource->itemActions()[$index] ?? false, ResponseAlias::HTTP_NOT_FOUND);
        abort_if(! $this->resource->can('viewAny', $this->resource->getModel()), ResponseAlias::HTTP_FORBIDDEN);

        $redirectRoute = redirect($this->resource->route('index'));

        if (request()->hasAny(['relatable_mode', 'redirect_back'])) {
            $redirectRoute = back();
        }

        try {
            $action->callback($item);
        } catch (Throwable $e) {
            throw_if(! app()->isProduction(), $e);
            report($e);

            return $redirectRoute
                ->with('alert', trans('moonshine::ui.saved_error'));
        }

        return $redirectRoute
            ->with('alert', $action->message());
    }

    public function bulk($index): RedirectResponse
    {
        $redirectRoute = redirect($this->resource->route('index'));

        if (request()->hasAny(['relatable_mode', 'redirect_back'])) {
            $redirectRoute = back();
        }

        if (! request('ids')) {
            return $redirectRoute;
        }

        abort_if(! $action = $this->resource->bulkActions()[$index] ?? false, ResponseAlias::HTTP_NOT_FOUND);
        abort_if(! $this->resource->can('viewAny', $this->resource->getModel()), ResponseAlias::HTTP_FORBIDDEN);

        try {
            $items = $this->resource->getModel()
                ->newModelQuery()
                ->findMany(explode(';', request('ids')));

            $items->each(fn ($item) => $action->callback($item));
        } catch (Throwable $e) {
            throw_if(! app()->isProduction(), $e);
            report($e);

            return $redirectRoute
                ->with('alert', trans('moonshine::ui.saved_error'));
        }

        return $redirectRoute
            ->with('alert', $action->message());
    }

    /**
     * @throws AuthorizationException
     */
    public function actions(): mixed
    {
        abort_if(! $this->resource->can('viewAny', $this->resource->getModel()), ResponseAlias::HTTP_FORBIDDEN);

        $actions = $this->resource->getActions();

        if ($actions->isNotEmpty()) {
            foreach ($actions as $action) {
                if ($action->isTriggered()) {
                    return $action->handle();
                }
            }
        }

        $redirectRoute = redirect($this->resource->route('index'));

        if (request()->hasAny(['relatable_mode', 'redirect_back'])) {
            $redirectRoute = back();
        }

        return $redirectRoute;
    }

    /**
     * @throws AuthorizationException|Throwable
     */
    public function index(string $uri = null): string|View
    {
        if ($uri && $this->resource->queryTags()) {
            $queryTag = collect($this->resource->queryTags())->first(fn (QueryTag $tag) => $tag->uri() === $uri);

            $this->resource->customBuilder($queryTag->builder());
        }

        abort_if(! $this->resource->can('viewAny', $this->resource->getModel()), ResponseAlias::HTTP_FORBIDDEN);


        if (request()->ajax()) {
            abort_if(! request()->hasAny(['related_column', 'related_key']), ResponseAlias::HTTP_NOT_FOUND);

            $this->resource->relatable(
                request('related_column'),
                request('related_key')
            );
        }

        $actions = $this->resource->getActions();

        $view = view($this->resource->baseIndexView(), [
            'resource' => $this->resource,
            'filters' => $this->resource->filters(),
            'dropdownActions' => $actions->filter(fn ($action) => $action->inDropdown()),
            'lineActions' => $actions->filter(fn ($action) => ! $action->inDropdown()),
            'metrics' => $this->resource->metrics(),
            'items' => $this->resource->paginate(),
        ]);

        if (request()->ajax()) {
            $sections = $view->renderSections();

            return $sections['content'] ?? '';
        }

        return $view;
    }

    /**
     * @throws AuthorizationException
     * @throws Throwable
     */
    public function create(): string|View|Factory|Redirector|RedirectResponse|Application
    {
        abort_if(! $this->resource->can('create', $this->resource->getModel()), ResponseAlias::HTTP_FORBIDDEN);


        if (!in_array('create', $this->resource->getActiveActions(), true)) {
            return redirect($this->resource->route('index'));
        }

        return $this->editView();
    }

    /**
     * @throws AuthorizationException
     * @throws Throwable
     */
    public function edit($id): string|View|Factory|Redirector|RedirectResponse|Application
    {
        if (!in_array('edit', $this->resource->getActiveActions(), true)) {
            return redirect($this->resource->route('index'));
        }

        $item = $this->resource->getModel()
            ->newModelQuery()
            ->findOrFail($id);

        abort_if(! $this->resource->can('update', $item), ResponseAlias::HTTP_FORBIDDEN);

        $this->resource->setItem($item);

        return $this->editView($item);
    }

    /**
     * @throws AuthorizationException
     * @throws Throwable
     */
    public function show($id): View|Factory|Redirector|Application|RedirectResponse
    {
        if (!in_array('show', $this->resource->getActiveActions(), true)) {
            return redirect($this->resource->route('index'));
        }

        $item = $this->resource->getModel()
            ->newModelQuery()
            ->findOrFail($id);

        abort_if(! $this->resource->can('view', $item), ResponseAlias::HTTP_FORBIDDEN);

        $this->resource->setItem($item);

        return view($this->resource->baseShowView(), [
            'resource' => $this->resource,
            'item' => $item ?? $this->resource->getModel(),
        ]);
    }

    /**
     * @throws AuthorizationException
     * @throws Throwable
     */
    public function update($id, Request $request): JsonResponse|Factory|View|Redirector|Application|RedirectResponse
    {
        if (!in_array('edit', $this->resource->getActiveActions(), true)) {
            return redirect($this->resource->route('index'));
        }

        $item = $this->resource->getModel()
            ->newModelQuery()
            ->findOrFail($id);

        abort_if(! $this->resource->can('update', $item), ResponseAlias::HTTP_FORBIDDEN);

        return $this->save($request, $item);
    }

    /**
     * @throws AuthorizationException
     * @throws Throwable
     */
    public function store(Request $request): JsonResponse|Factory|View|Redirector|Application|RedirectResponse
    {
        if (!in_array('edit', $this->resource->getActiveActions(), true)
            && !in_array('create', $this->resource->getActiveActions(), true)) {
            return redirect($this->resource->route('index'));
        }

        $item = $this->resource->getModel();

        abort_if(! $this->resource->can('create', $item), ResponseAlias::HTTP_FORBIDDEN);

        return $this->save($request, $item);
    }

    /**
     * @throws AuthorizationException
     */
    public function destroy($id): Redirector|Application|RedirectResponse
    {
        if (!in_array('delete', $this->resource->getActiveActions(), true)) {
            return redirect($this->resource->route('index'));
        }

        $redirectRoute = redirect($this->resource->route('index'));

        if (request()->has('ids')) {
            abort_if(! $this->resource->can('massDelete', $this->resource->getModel()), ResponseAlias::HTTP_FORBIDDEN);

            $this->resource->massDelete(
                explode(';', request('ids'))
            );
        } else {
            $item = $this->resource->getModel()
                ->newModelQuery()
                ->findOrFail($id);

            abort_if(! $this->resource->can('delete', $item), ResponseAlias::HTTP_FORBIDDEN);

            $this->resource->delete($item);
        }

        if (request()->hasAny(['relatable_mode', 'redirect_back'])) {
            $redirectRoute = back();
        }

        return $redirectRoute
            ->with('alert', trans('moonshine::ui.deleted'));
    }

    /**
     * @throws Throwable
     */
    protected function editView(Model $item = null): View|string
    {
        if (is_null($item) && request('relatable_mode') && request('related_column')) {
            $item = $this->resource->getModel();
            $item->{request('related_column')} = request('related_key');
        }

        $view = view($this->resource->baseEditView(), [
            'resource' => $this->resource,
            'item' => $item ?? $this->resource->getModel(),
        ]);

        if (request()->ajax()) {
            $sections = $view->renderSections();

            return $sections['content'] ?? '';
        }

        return $view;
    }

    /**
     * @throws Throwable
     */
    protected function save(Request $request, Model $item): JsonResponse|Factory|View|Redirector|Application|RedirectResponse
    {
        if ($request->isMethod('post') || $request->isMethod('put')) {
            $redirectRoute = redirect($this->resource->route($item->exists ? 'edit' : 'create', $item->getKey()));

            $validator = $this->resource->validate($item);

            if (request()->has('relatable_mode')) {
                $redirectRoute = back();
            }

            if ($request->isAttemptingPrecognition()) {
                return response()->json(
                    $validator->errors(),
                    $validator->fails()
                        ? ResponseAlias::HTTP_UNPROCESSABLE_ENTITY
                        : ResponseAlias::HTTP_OK
                );
            }

            if ($validator->fails()) {
                return $redirectRoute
                    ->withErrors($validator)
                    ->withInput();
            }

            try {
                $this->resource->save($item);
            } catch (ResourceException $e) {
                throw_if(! app()->isProduction(), $e);
                report($e);

                return $redirectRoute
                    ->with('alert', trans('moonshine::ui.saved_error'));
            }

            $redirectRoute = redirect($this->resource->route('index'));

            if (request()->has('relatable_mode')) {
                $redirectRoute = back();
            }

            return $redirectRoute
                ->with('alert', trans('moonshine::ui.saved'));
        }

        return $this->editView($item);
    }
}
