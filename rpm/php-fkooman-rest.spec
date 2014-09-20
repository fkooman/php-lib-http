%global composer_vendor  fkooman
%global composer_project rest

%global github_owner     fkooman
%global github_name      php-lib-rest

Name:       php-%{composer_vendor}-%{composer_project}
Version:    0.4.4
Release:    1%{?dist}
Summary:    Simple PHP library for writing REST services

Group:      System Environment/Libraries
License:    ASL 2.0
URL:        https://github.com/%{github_owner}/%{github_name}
Source0:    https://github.com/%{github_owner}/%{github_name}/archive/%{version}.tar.gz
BuildArch:  noarch

Provides:   php-composer(%{composer_vendor}/%{composer_project}) = %{version}

Requires:   php >= 5.3.3

Requires:   php-composer(fkooman/json) >= 0.5.1
Requires:   php-composer(fkooman/json) < 0.6.0

%description
Library written in PHP to make it easy to develop REST applications.

%prep
%setup -qn %{github_name}-%{version}

%build

%install
mkdir -p ${RPM_BUILD_ROOT}%{_datadir}/php
cp -pr src/* ${RPM_BUILD_ROOT}%{_datadir}/php

%files
%defattr(-,root,root,-)
%dir %{_datadir}/php/%{composer_vendor}/Http
%dir %{_datadir}/php/%{composer_vendor}/Rest
%{_datadir}/php/%{composer_vendor}/Http/*
%{_datadir}/php/%{composer_vendor}/Rest/*
%doc README.md CHANGES.md COPYING composer.json

%changelog
* Sat Sep 20 2014 François Kooman <fkooman@tuxed.net> - 0.4.4-1
- update to 0.4.4

* Tue Sep 16 2014 François Kooman <fkooman@tuxed.net> - 0.4.3-1
- update to 0.4.3

* Tue Sep 16 2014 François Kooman <fkooman@tuxed.net> - 0.4.2-1
- update to 0.4.2

* Mon Sep 15 2014 François Kooman <fkooman@tuxed.net> - 0.4.1-1
- update to 0.4.1

* Fri Aug 29 2014 François Kooman <fkooman@tuxed.net> - 0.4.0-2
- use github tagged release sources
- update group to System Environment/Libraries

* Sat Aug 16 2014 François Kooman <fkooman@tuxed.net> - 0.4.0-1
- initial package
