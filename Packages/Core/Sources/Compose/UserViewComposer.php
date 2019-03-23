<?php
namespace Packages\Core\Sources\Compose;

use Illuminate\Contracts\View\View;

class UserViewComposer
{
    /**
     * @param $view
     */
    public function compose(View $view)
    {
        $currentUser = !auth()->check() ?: auth()->user();
        $view->with('currentUser', $currentUser);
    }

}