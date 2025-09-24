#!/bin/bash

TMPDIR=tempdir_$$

mkdir $TMPDIR
cd $TMPDIR
7za e ../$1
7za a ../${1%.7z}.zip *
cd ..
rm -rf $TMPDIR    
