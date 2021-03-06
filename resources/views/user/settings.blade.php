@extends('global.master')

@section('content')
<div>
    <ul class="nav nav-tabs">
        <li class="nav-item">
            <a class="nav-link active" href="#profile" data-toggle="tab">
                <span class="fa fa-user"></span>
                {{ strans('common.profile')->upperCaseFirst() }}
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="#settings" data-toggle="tab">
                <i class="fa fa-wrench"></i>
                {{ strans('common.settings')->upperCaseFirst() }}
            </a>
        </li>

        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#">
                <i class="fa fa-lock"></i>
                {{ strans('common.account')->upperCaseFirst() }}
                <span class="caret"></span>
            </a>
            <div class="dropdown-menu">
                <a class="dropdown-item" href="#password" data-toggle="tab">Zmiana hasła</a>
                <a class="dropdown-item" href="#email" data-toggle="tab">Zmiana adresu email</a>
            </div>
        </li>

        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#">
                {{ strans('common.domains')->upperCaseFirst() }} <span class="caret"></span>
            </a>
            <div class="dropdown-menu">
                <a class="dropdown-item" href="#domains" data-toggle="tab">Zablokowane</a>
            </div>
        </li>

        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#">
                {{ strans('common.groups')->upperCaseFirst() }} <span class="caret"></span>
            </a>
            <div class="dropdown-menu">
                <a class="dropdown-item" href="#subscribed" data-toggle="tab">
                    {{ strans('groups.subscribed')->upperCaseFirst() }}
                </a>
                <a class="dropdown-item" href="#moderated" data-toggle="tab">
                    {{ strans('groups.moderated')->upperCaseFirst() }}
                </a>
                <a class="dropdown-item" href="#blocked" data-toggle="tab">
                    {{ strans('groups.blocked')->upperCaseFirst() }}
                </a>
                <a class="dropdown-item" href="#bans" data-toggle="tab">
                    {{ strans('groups.banned')->upperCaseFirst() }}
                </a>
            </div>
        </li>

        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#">
                {{ strans('common.users')->upperCaseFirst() }} <span class="caret"></span>
            </a>
            <div class="dropdown-menu">
                <a class="dropdown-item" href="#blockedusers" data-toggle="tab">Zablokowani użytownicy</a>
            </div>
        </li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade in active" id="profile">
            @include('user.settings.profile')
        </div>

        <div class="tab-pane fade" id="password">
            @include('user.settings.change_password')
        </div>

        <div class="tab-pane fade" id="email">
            @include('user.settings.change_email')
        </div>

        <div class="tab-pane fade" id="settings">
            @include('user.settings.settings')
        </div>

        <div class="tab-pane fade" id="subscribed">
            @include('user.settings.subscribed_groups')
        </div>

        <div class="tab-pane fade" id="moderated">
            @include('user.settings.moderated_groups')
        </div>

        <div class="tab-pane fade" id="blocked">
            @include('user.settings.blocked_groups')
        </div>

        <div class="tab-pane fade" id="bans">
            @include('user.settings.bans')
        </div>

        <div class="tab-pane fade" id="blockedusers">
            @include('user.settings.blocked_users')
        </div>

        <div class="tab-pane fade" id="domains">
            @include('user.settings.blocked_domains')
        </div>
    </div>
</div>
@stop

