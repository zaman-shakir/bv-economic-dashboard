@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 dark:focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-600/20 rounded-lg shadow-sm transition-all duration-200']) }}>
