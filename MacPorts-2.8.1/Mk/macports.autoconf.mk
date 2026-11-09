# vim:ts=8:noet:sts=8:sw=8

SHELL			= /bin/sh

CC			= clang
CFLAGS			= -g -O2 -std=c99 $(CFLAGS_QUICHEEATERS) $(CFLAGS_PEDANTIC) $(CFLAGS_WERROR)
CPPFLAGS		=  -DHAVE_CONFIG_H -I/Users/digiweb/Webserver/SIGWEB_ERSUMA/SIGWEB-API/MacPorts-2.8.1/src -I/Users/digiweb/Webserver/SIGWEB_ERSUMA/SIGWEB-API/MacPorts-2.8.1/src -I. -I/Users/digiweb/Webserver/SIGWEB_ERSUMA/SIGWEB-API/MacPorts-2.8.1/vendor/vendor-destroot/opt/local/libexec/macports/include
CFLAGS_QUICHEEATERS	= -Wextra -Wall
CFLAGS_PEDANTIC		= -pedantic
CFLAGS_WERROR		= 

UNIVERSAL_ARCHFLAGS	=  -arch arm64 -arch x86_64

READLINE_CFLAGS		=
MD5_CFLAGS		=
SQLITE3_CPPFLAGS	= -I/Users/digiweb/Webserver/SIGWEB_ERSUMA/SIGWEB-API/MacPorts-2.8.1/vendor/tcl/pkgs/sqlite3.40.0/compat/sqlite3
CURL_CFLAGS		= 

LDFLAGS			= 
SHLIB_LD		= ${CC} -dynamiclib ${CFLAGS} ${LDFLAGS} -Wl,-single_module
STLIB_LD		= ${AR} cr
SHLIB_CFLAGS		= -fno-common
SHLIB_LDFLAGS		= 
SHLIB_SUFFIX		= .dylib
TCL_STUB_LIB_SPEC	= -L/Users/digiweb/Webserver/SIGWEB_ERSUMA/SIGWEB-API/MacPorts-2.8.1/vendor/tcl/unix -ltclstub8.6

LIBS			= 
READLINE_LIBS		= 
MD5_LIBS		= 
SQLITE3_LIBS		= /Users/digiweb/Webserver/SIGWEB_ERSUMA/SIGWEB-API/MacPorts-2.8.1/vendor/tcl/unix/pkgs/sqlite3.40.0/libsqlite3.40.0$(SHLIB_SUFFIX)
CURL_LIBS		= -lcurl
INSTALL			= /usr/bin/install -c
MTREE			= /usr/sbin/mtree
LN_S			= ln -s
XCODEBUILD		= /usr/bin/xcodebuild

INTREE_TCLSH		= /Users/digiweb/Webserver/SIGWEB_ERSUMA/SIGWEB-API/MacPorts-2.8.1/vendor/tclsh
TCLSH			= /opt/local/libexec/macports/bin/tclsh8.6
TCL_PACKAGE_PATH	= /opt/local/libexec/macports/lib

DSCL			= /usr/bin/dscl
DSEDITGROUP		= /usr/sbin/dseditgroup
DSTUSR			= root
DSTGRP			= admin
DSTMODE			= 0755
RUNUSR			= macports

prefix			= /opt/local
sysconfdir		= ${prefix}/etc
exec_prefix		= ${prefix}
bindir			= ${exec_prefix}/bin
datarootdir		= ${prefix}/share
datadir			= ${datarootdir}
libdir			= ${exec_prefix}/lib
localstatedir		= ${prefix}/var
infodir			= ${datarootdir}/info

mpconfigdir		= ${sysconfdir}/macports

MACPORTS_VERSION	= 2.8.1

SILENT			= @

HAVE_GETDELIM		= yes
HAVE_GETLINE		= yes
HAVE_STRLCAT		= yes
HAVE_STRLCPY		= yes
