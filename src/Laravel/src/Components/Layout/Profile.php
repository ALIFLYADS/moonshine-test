<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Components\Layout;

use Closure;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Storage;
use MoonShine\Laravel\MoonShineAuth;
use MoonShine\Laravel\Pages\ProfilePage;
use MoonShine\UI\Components\MoonShineComponent;
use Throwable;

/**
 * @method static static make(?string $route = null, ?string $logOutRoute = null, ?Closure $avatar = null, ?Closure $nameOfUser = null, ?Closure $username = null, bool $withBorder = false, ?string $guard = null)
 */
final class Profile extends MoonShineComponent
{
    protected string $view = 'moonshine::components.layout.profile';

    protected ?string $defaultAvatar = null;

    private readonly ?Authenticatable $user;

    public function __construct(
        protected ?string $route = null,
        protected ?string $logOutRoute = null,
        protected ?Closure $avatar = null,
        protected ?Closure $nameOfUser = null,
        protected ?Closure $username = null,
        protected bool $withBorder = false,
        protected ?string $guard = null,
    ) {
        parent::__construct();

        $this->user = MoonShineAuth::getGuard($guard)->user();
    }

    public function isWithBorder(): bool
    {
        return $this->withBorder;
    }

    public function avatarPlaceholder(string $url): self
    {
        $this->defaultAvatar = $url;

        return $this;
    }

    public function getAvatarPlaceholder(): string
    {
        return $this->defaultAvatar ?? moonshineAssets()->getAsset('vendor/moonshine/avatar.jpg');
    }

    /**
     * @return array<string, mixed>
     * @throws Throwable
     */
    protected function viewData(): array
    {
        $nameOfUser = \is_null($this->nameOfUser)
            ? $this->getDefaultName()
            : value($this->nameOfUser, $this);

        $username = \is_null($this->username)
            ? $this->getDefaultUsername()
            : value($this->username, $this);

        $avatar = \is_null($this->avatar)
            ? $this->getDefaultAvatar()
            : value($this->avatar, $this);

        return [
            'route' => $this->route ?? toPage(
                moonshineConfig()->getPage('profile', ProfilePage::class)
            ),
            'logOutRoute' => $this->logOutRoute ?? moonshineRouter()->to('logout'),
            'avatar' => $avatar,
            'nameOfUser' => $nameOfUser,
            'username' => $username,
            'withBorder' => $this->isWithBorder(),
        ];
    }

    private function getDefaultName(): string
    {
        $userField = moonshineConfig()->getUserField('name');

        if ($userField === '') {
            return $this->getDefaultUsername();
        }

        if ($userField === false) {
            return '';
        }

        return $this->user->{$userField} ?? '';
    }

    private function getDefaultUsername(): string
    {
        $userField = moonshineConfig()->getUserField('username', 'email');

        if ($userField === false) {
            return '';
        }

        return $this->user->{$userField} ?? '';
    }

    private function getDefaultAvatar(): string
    {
        $userField = moonshineConfig()->getUserField('avatar');

        if ($userField === '') {
            return $this->getAvatarPlaceholder();
        }

        if ($userField === false) {
            return '';
        }

        $avatar = $this->user?->{$userField};

        return $avatar
            ? Storage::disk(moonshineConfig()->getDisk())->url($avatar)
            : $this->getAvatarPlaceholder();
    }
}
