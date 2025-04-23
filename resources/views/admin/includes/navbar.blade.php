<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="{{ route('admin.dashboard') }}" class="nav-link">{{__('messages.Home')}}</a>
      </li>

      <li class="nav-item d-none d-sm-inline-block">
        <a href="{{ route('admin.logout') }}" class="nav-link">{{__('messages.Logout')}}</a>
      </li>
        @foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
        <a class="nav-link"  hreflang="{{ $localeCode }}" href="{{ LaravelLocalization::getLocalizedURL($localeCode, null, [], true) }}">
            {{ $properties['native'] }}
        </a>
        @endforeach
        <li class="nav-item d-none d-sm-inline-block">
        @if($user->can('day-close') && \DB::table('days')->where('is_open',true)->count() > 0)
            <a href="{{ route('day.close') }}" class="nav-link">
                <span> {{__('messages.Close Day')}} </span>
                <i class="fa fa-times text-danger"></i>
            </a>
        @elseif($user->can('day-add') && \DB::table('days')->where('is_open',true)->count() < 1)
            <a href="{{ route('day.open') }}" class="nav-link">
                <span> {{__('messages.Add Day')}} </span>
                <i class="fa fa-plus text-primary"></i>
            </a>
        @endif
      </li>
    </ul>


  </nav>
