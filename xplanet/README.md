# Xplanet

The moon and earth endpoints require the `xplanet` executable to be located inside this directory.

The repo at https://github.com/jpmeijers/xplanet can be used to compile a statically linked binary of xplanet using the following command:

```
git clone https://github.com/jpmeijers/xplanet.git
cd xplanet
mkdir -p build
cd build
../configure LDFLAGS="-static -static-libgcc -static-libstdc++"
make
cd src
cp xplanet <this api>/moon/view
```

Make sure xplanet has JPEG and PNG support.
If it does not, it means `libjpeg-dev` or `libpng-dev` were missing during configuration.

```
$ ./xplanet --version
Xplanet 1.3.1
Copyright (C) 2012 Hari Nair <hari@alumni.caltech.edu>
The latest version can be found at http://xplanet.sourceforge.net
Compiled with support for:
        JPEG
        PNG
```
