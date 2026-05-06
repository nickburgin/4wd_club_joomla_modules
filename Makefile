.PHONY: all clean list pkg_gafinance pkg_gatripsys pkg_gausers

all:
	./build.sh all

pkg_gafinance:
	./build.sh pkg_gafinance

pkg_gatripsys:
	./build.sh pkg_gatripsys

pkg_gausers:
	./build.sh pkg_gausers

list:
	./build.sh list

clean:
	rm -rf dist/
