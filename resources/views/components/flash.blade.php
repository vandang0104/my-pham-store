<div class="flash-stack">
    @foreach ($messages as $flash)
        <x-flash-message :type="$flash['type']" :message="$flash['message']" />
    @endforeach
</div>
