<div :class="sidebarOpen ? 'block' : 'hidden'" x-on:click="sidebarOpen = false"
    class="fixed z-20 inset-0 opacity-50 transition-opacity lg:hidden"></div>

<div :class="sidebarOpen ? 'translate-x-0 ease-out' : '-translate-x-full ease-in'"
    class="fixed z-30 inset-y-0 left-0 w-64 transition duration-300 transform bg-neutral overflow-y-auto lg:translate-x-0 lg:static lg:inset-0">

    <div class="flex items-center justify-center mt-8">
        <div class="flex items-center justify-center">
            <img src="{{ asset('img/logo2.svg') }}" width="50%" />
        </div>
    </div>

    <nav class="mt-10">
        {!! Base::menu(app(\Sdkconsultoria\Base\Services\MenuService::class)->getMenu()) !!}
    </nav>
</div>
