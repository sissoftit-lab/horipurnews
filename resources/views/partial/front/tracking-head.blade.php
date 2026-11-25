@php($__meta = app(\App\Services\Tracking\MetaTrackingService::class))
@if ($__meta->enabled())
<meta name="csrf-token" content="{{ csrf_token() }}">
<script>
	window.TRACKING = {
		endpoint: "{{ route('tracking.event') }}",
		server: {{ $__meta->serverEnabled() ? 'true' : 'false' }},
		page: @json($trackPage ?? 'other'),
		params: @json($trackParams ?? new \stdClass)
	};
</script>
<!-- Meta Pixel -->
<script>
!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '{{ $__meta->pixelId() }}');
</script>
<script src="{{ asset('assets/front/js/tracking.js') }}?v=1" defer></script>
@endif
