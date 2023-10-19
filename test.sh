#!/bin/zsh


echo 'Copying files to myparcelnlprod...'
rm -rf ../myparcelnlprod/
cp -R dist/myparcelnl ../myparcelnlprod/

if [ -f ../myparcelnlprod/myparcelnl.php ]; then
  echo 'Renaming myparcelnl.php to myparcelnlprod.php'
  mv ../myparcelnlprod/myparcelnl.php ../myparcelnlprod/myparcelnlprod.php
fi

echo 'Replacing class MyParcelNL with class MyParcelNLProd...'
sed -i '' 's/class MyParcelNL/class MyParcelNLProd/g' ../myparcelnlprod/myparcelnlprod.php
sed -i '' "s/'myparcelnl'/'myparcelnlprod'/g" ../myparcelnlprod/myparcelnlprod.php

tree ../myparcelnlprod -I 'vendor|node_modules' -L 2
