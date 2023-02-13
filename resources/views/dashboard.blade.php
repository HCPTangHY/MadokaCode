<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @if (is_null(Auth::user()->privilege))
                        您是游客，请联系管理员进行认证
                    @else
                        我趣，你是
                        @if(Auth::user()->privilege == 0)
                            超级管理员
                        @elseif(Auth::user()->privilege ==1)
                            管理员
                        @else
                            普通用户
                        @endif
                        !
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
