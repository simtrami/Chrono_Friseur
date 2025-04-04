<span
    class="inline-flex items-center gap-x-1.5 rounded-full px-2 py-1 text-xs font-medium text-gray-900 ring-1 ring-gray-200 ring-inset"
    {{ $attributes }}>
  <svg viewBox="0 0 6 6" aria-hidden="true" class="size-1.5 fill-black"
       :class="{
            '!fill-red-500': color === 'red',
            '!fill-orange-500': color === 'orange',
            '!fill-amber-500': color === 'amber',
            '!fill-lime-500': color === 'lime',
            '!fill-green-500': color === 'green',
            '!fill-emerald-500': color === 'emerald',
            '!fill-teal-500': color === 'teal',
            '!fill-cyan-500': color === 'cyan',
            '!fill-sky-500': color === 'sky',
            '!fill-blue-500': color === 'blue',
            '!fill-indigo-500': color === 'indigo',
            '!fill-violet-500': color === 'violet',
            '!fill-purple-500': color === 'purple',
            '!fill-fuchsia-500': color === 'fuchsia',
            '!fill-pink-500': color === 'pink',
            '!fill-rose-500': color === 'rose',
            '!fill-slate-500': color === 'slate',
            '!fill-stone-500': color === 'stone'
        }"
  >
    <circle cx="3" cy="3" r="3"/>
  </svg>
  {{ $slot }}
</span>
