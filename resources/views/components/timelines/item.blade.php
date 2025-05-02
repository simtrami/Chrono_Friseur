<li class="flex items-center justify-between gap-x-6 py-5">
    <div class="flex items-center justify-start min-w-0 space-x-6">
        <template x-if="timeline.picture !== null">
        <img :src="timeline.picture" alt="Illustration"
             @click="window.location.href = `/${user.username}/${timeline.slug}`"
             class="size-12 object-cover flex-none rounded-full bg-gray-50 cursor-pointer"
        >
        </template>
        <p x-text="timeline.name" @click="window.location.href = `/${user.username}/${timeline.slug}`"
           class="text-md/6 font-semibold text-gray-900 cursor-pointer"
        ></p>
        <div class="text-xs/5 text-gray-500">
            <p x-text="timeline.description ?? ''" class="line-clamp-2 max-w-5xl"></p>
            <p>
                <time :datetime="moment(timeline.created_at).format()"
                      x-text="moment(timeline.created_at).calendar()"
                ></time>
            </p>
        </div>
    </div>
    <div class="flex flex-none items-center gap-x-4">
        <a :href="`/${timeline.created_by}/${timeline.slug}`"
           class="hidden rounded-md bg-white px-2.5 py-1.5 text-sm font-semibold text-gray-900 shadow-xs ring-1 ring-gray-300 ring-inset hover:bg-gray-50 sm:block"
        >Ouvrir</a>
        <div x-data x-menu class="relative flex-none">
            <button x-menu:button x-tooltip="'Actions'" type="button"
                    id="options-menu-0-button" aria-expanded="false" aria-haspopup="true"
                    class="rounded-full -m-2.5 block p-1.5 text-gray-500 hover:text-gray-900"
            >
                <x-icons.ellipsis class="rotate-90 size-5"/>
            </button>

            <div x-menu:items x-transition.origin.top.left x-cloak
                 role="menu" aria-orientation="vertical" aria-labelledby="options-menu-0-button" tabindex="-1"
                 class="absolute right-0 z-10 mt-4 w-28 origin-top-right rounded-md bg-white py-2 shadow-lg ring-1 ring-gray-900/5 focus:outline-hidden"
            >
                <button x-menu:item @click="$el.blur(); showForm(timeline.id)" role="menuitem"
                        :class="{ 'bg-gray-50': $menuItem.isActive, 'opacity-50 cursor-not-allowed': $menuItem.isDisabled }"
                        class="w-full flex items-center px-3 py-1 text-sm/6 text-gray-900 text-left transition-colors focus:outline-none"
                >Modifier
                </button>
                <button x-menu:item @click="$el.blur(); deleteTimeline(timeline.id)" role="menuitem"
                        :class="{ 'bg-gray-50': $menuItem.isActive, 'opacity-50 cursor-not-allowed': $menuItem.isDisabled }"
                        class="w-full flex items-center px-3 py-1 text-sm/6 text-gray-900 text-left transition-colors focus:outline-none"
                >Supprimer
                </button>
            </div>
        </div>
    </div>
</li>
