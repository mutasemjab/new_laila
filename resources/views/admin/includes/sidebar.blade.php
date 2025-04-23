<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="#" class="brand-link">
        <img src="{{ asset('assets/admin/dist/img/AdminLTELogo.png') }}" alt="AdminLTE Logo"
            class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light">Alien-code</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="{{ asset('assets/admin/dist/img/user2-160x160.jpg') }}" class="img-circle elevation-2"
                    alt="User Image">
            </div>
            <div class="info">
                <a href="#" class="d-block">{{ auth()->user()->name }}</a>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <!-- Add icons to the links using the .nav-icon class
                     with font-awesome or any other icon font library -->


                {{-- Home start --}}
                <li class="nav-item">
                    <a href="{{ route('admin.dashboard') }}" class="nav-link">
                        <i class="fa fa-home nav-icon"></i>
                        <p>{{ __('messages.Home') }}</p>
                    </a>
                </li>
                {{-- Home end --}}

                {{-- days start --}}
                @if($user->can('day-table'))
                <li class="nav-item">
                    <a href="{{ route('day.index') }}" class="nav-link">
                        <i class="far fa fa-calendar nav-icon"></i>
                        <p>{{ __('messages.Days statics') }}</p>
                    </a>
                </li>
                @endif
                {{-- days end --}}

                {{-- Qualified start --}}
                @if($user->can('day-table'))
                <li class="nav-item">
                    <a href="{{ route('day.qualified') }}" class="nav-link">
                        <i class="fa fa-certificate nav-icon"></i>
                        <p>{{ __('messages.Qualified users') }}</p>
                    </a>
                </li>
                @endif
                {{-- Qualified end --}}


                @if (
                $user->can('user-table') ||
                $user->can('user-add') ||
                $user->can('user-edit') ||
                $user->can('user-delete'))
                <li class="nav-item">
                    <a href="{{ route('users.index') }}" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p> {{__('messages.users')}} </p>
                    </a>
                </li>
                @endif

{{--
                <li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>
                            {{ __('messages.reports') }}
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('inventory_report') }}" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p> {{ __('messages.inventory_report_with_costs') }} </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('order_report') }}" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p> {{ __('messages.order_report') }} </p>
                            </a>
                        </li>


                    </ul>
                </li> --}}




                {{-- <li class="nav-item">
                    <a href="{{ route('admin.setting.index') }}" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>{{__('messages.Settings')}} </p>
                    </a>
                </li> --}}



                <li class="nav-item">
                    <a href="{{ route('admin.login.edit',auth()->user()->id) }}" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>{{__('messages.Admin_account')}} </p>
                    </a>
                </li>

                @if ($user->can('role-table') || $user->can('role-add') || $user->can('role-edit') ||
                $user->can('role-delete'))
                <li class="nav-item">
                    <a href="{{ route('admin.role.index') }}" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <span>{{__('messages.Roles')}} </span>
                    </a>
                </li>
                @endif

                @if (
                $user->can('employee-table') ||
                $user->can('employee-add') ||
                $user->can('employee-edit') ||
                $user->can('employee-delete'))
                <li class="nav-item">
                    <a href="{{ route('admin.employee.index') }}" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <span> {{__('messages.Employee')}} </span>
                    </a>
                </li>
                @endif


                @php $rooms = 0 ; $rooms = \App\Models\Room::all(); @endphp

                @if ( $rooms->count() &&
                ($user->can('room-table') ||
                $user->can('room-add') ||
                $user->can('room-edit') ||
                $user->can('room-delete')) )

                    <li class="nav-item">
                        <a href="{{ route('rooms.index') }}" class="nav-link">
                            <i class="far fa-circle nav-icon"></i>
                            <p> {{__('messages.rooms')}} </p>
                        </a>
                    </li>
                    @foreach($rooms as $room)
                    <li class="nav-item">
                        <a href="{{ route('room.attandance',[$room->id,$room->slug()]) }}" class="nav-link">
                            <i class="far fa-circle nav-icon"></i>
                            <p> {{ $room->name }} </p>
                        </a>
                    </li>
                    @endforeach

                @endif

                @if (
                    $user->can('print-table') ||
                    $user->can('print-add') ||
                    $user->can('print-edit') ||
                    $user->can('print-delete'))
                    <li class="nav-item">
                        <a href="{{ route('admin.print.badge') }}" class="nav-link">
                            <i class="far fa-circle nav-icon"></i>
                            <span> {{__('messages.Print Badges')}} </span>
                        </a>
                    </li>
                @endif


            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>
