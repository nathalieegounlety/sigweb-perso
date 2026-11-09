TCL_SUBDIR=			tcl/unix
TCL_TARGETS=		install-binaries install-headers install-libraries install-msgs install-packages

TCLX_SUBDIR=		tclx
TCLX_TARGETS=		install-binaries install-libraries

TCLLIB_SUBDIR=		tcllib
TCLLIB_TARGETS= 	install-libraries

PREFIX=			/opt/local
DESTROOT=   		/Users/digiweb/Webserver/SIGWEB_ERSUMA/SIGWEB-API/MacPorts-2.8.1/vendor/vendor-destroot

.PHONY: all clean distclean install destroot test
.PHONY: destroot-tcl destroot-tclx destroot-tcllib
.PHONY: install-tcl install-tclx install-tcllib
.PHONY: %-tcl %-tclx %-tcllib

# for make all, run destroot (where destroot will depend on all of each
# subpackage)
all: destroot

%-tcl:
	@echo ===\> making $(@:%-tcl=%) in ${DIRPRFX}tcl/unix
	@umask 0022; $(MAKE) -C tcl/unix $(@:%-tcl=%)

%-tclx:
	@echo ===\> making $(@:%-tclx=%) in ${DIRPRFX}tclx
	@umask 0022; $(MAKE) -C tclx $(@:%-tclx=%)

# tclx also links against libtclstub
all-tclx: all-tcl

%-tcllib:
	@echo ===\> making $(@:%-tcllib=%) in ${DIRPRFX}tcllib
	@umask 0022; $(MAKE) -C tcllib TCLSH_PROG=/Users/digiweb/Webserver/SIGWEB_ERSUMA/SIGWEB-API/MacPorts-2.8.1/vendor/tclsh $(@:%-tcllib=%)

# tcllib requires a working tclsh
all-tcllib: all-tcl

destroot: destroot-tcl destroot-tclx destroot-tcllib
destroot-tcl: all-tcl
	@echo ===\> staging to destroot in ${DIRPRFX}tcl/unix
	@umask 0022; $(MAKE) -C tcl/unix DESTDIR=${DESTROOT} install-binaries install-headers install-libraries install-msgs install-packages

destroot-tclx: all-tclx
	@echo ===\> staging to destroot in ${DIRPRFX}tclx
	@umask 0022; $(MAKE) -C tclx DESTDIR=${DESTROOT} install-binaries install-libraries

destroot-tcllib: all-tcllib
	@echo ===\> staging to destroot in ${DIRPRFX}tcllib
	@umask 0022; $(MAKE) -C tcllib DESTDIR=${DESTROOT} TCLSH_PROG=/Users/digiweb/Webserver/SIGWEB_ERSUMA/SIGWEB-API/MacPorts-2.8.1/vendor/tclsh install-libraries
	@chmod -R ugo+rX ${DESTROOT}${PREFIX}/libexec/macports/lib/tcllib*

install: install-tcl install-tclx install-tcllib

TCL_PACKAGE_PATH=/opt/local/libexec/macports/lib
TCL_PREFIX=/opt/local/libexec/macports
OLD_TCL_FILES=	$(DESTDIR)$(TCL_PACKAGE_PATH)/tcl8/8.4/http-2.7.1{2,3}.tm \
				$(DESTDIR)$(TCL_PACKAGE_PATH)/tcl8/8.6/http-2.9.5.tm \
				$(DESTDIR)$(TCL_PACKAGE_PATH)/tcl8/8.4/platform-1.0.1{2,3,4,8}.tm \
				$(DESTDIR)$(TCL_PACKAGE_PATH)/tcl8/8.5/msgcat-1.5.2.tm \
				$(DESTDIR)$(TCL_PACKAGE_PATH)/tcl8/8.5/tcltest-2.3.{5,8}.tm \
				$(DESTDIR)$(TCL_PACKAGE_PATH)/tcl8/8.5/tcltest-2.5.3.tm \
				$(DESTDIR)$(TCL_PACKAGE_PATH)/tcl8/8.6/tdbc/sqlite3-1.1.3.tm \
				$(DESTDIR)$(TCL_PACKAGE_PATH)/tcl8.5 \
				$(DESTDIR)$(TCL_PREFIX)/bin/tclsh8.5 \
				$(DESTDIR)$(TCL_PREFIX)/lib/libtcl8.5.dylib \
				$(DESTDIR)$(TCL_PREFIX)/lib/libtclstub8.5.a \
				$(DESTDIR)$(TCL_PACKAGE_PATH)/thread2.7.{0,2,3} \
				$(DESTDIR)$(TCL_PACKAGE_PATH)/thread2.8.7 \
				$(DESTDIR)$(TCL_PACKAGE_PATH)/itcl4.2.2 \
				$(DESTDIR)$(TCL_PACKAGE_PATH)/sqlite3.36.0 \
				$(DESTDIR)$(TCL_PACKAGE_PATH)/tdbc{,mysql,odbc,postgres}1.1.3

install-tcl:
	rm -rf $(OLD_TCL_FILES)
	@echo ===\> making $(@:%-tcl=%) in ${DIRPRFX}tcl/unix
	@umask 0022; $(MAKE) -C tcl/unix install-binaries install-headers install-libraries install-msgs install-packages

install-tclx:
	rm -rf $(DESTDIR)$(TCL_PACKAGE_PATH)/tclx8.4
	@echo ===\> making $(@:%-tclx=%) in ${DIRPRFX}tclx
	@umask 0022; $(MAKE) -C tclx install-binaries install-libraries

install-tcllib:
	rm -rf $(DESTDIR)$(TCL_PACKAGE_PATH)/tcllib1.1{5,7,8}
	@echo ===\> making $(@:%-tcllib=%) in ${DIRPRFX}tcllib
	@umask 0022; $(MAKE) -C tcllib TCLSH_PROG=/Users/digiweb/Webserver/SIGWEB_ERSUMA/SIGWEB-API/MacPorts-2.8.1/vendor/tclsh install-libraries
	@chmod -R ugo+rX $(DESTDIR)${PREFIX}/libexec/macports/lib/tcllib*

test:

clean: clean-tcl clean-tcllib clean-tclx
	rm -rf ${DESTROOT}

distclean: distclean-tcl distclean-tcllib distclean-tclx
	rm -f Makefile
	rm -rf vendor-destroot
	rm -f tclsh

codesign:: tcl/unix/libtcl8.6.dylib tcl/unix/tclsh tclx/libtclx8.6.dylib tcl/unix/pkgs/itcl4.2.3/libitcl4.2.3.dylib tcl/unix/pkgs/tdbc1.1.5/libtdbc1.1.5.dylib tcl/unix/pkgs/tdbcpostgres1.1.5/libtdbcpostgres1.1.5.dylib tcl/unix/pkgs/thread2.8.8/libthread2.8.8.dylib tcl/unix/pkgs/tdbcmysql1.1.5/libtdbcmysql1.1.5.dylib tcl/unix/pkgs/tdbcodbc1.1.5/libtdbcodbc1.1.5.dylib tcl/unix/pkgs/sqlite3.40.0/libsqlite3.40.0.dylib
	../src/codesign.sh $?

