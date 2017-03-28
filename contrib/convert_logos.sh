#!/bin/sh

for i in $(ls "${1}"/*.orig)
do
    origLogoFile=$(basename "${i}" .orig)
    echo "${1}/${origLogoFile}"

    # convert without "upscaling", i.e. if this source image is smaller than 64x48, do not
    # make it bigger, but place it "as-is" in the 64x48 frame
    #convert ${1}/${origLogoFile}.orig -resize 64x48\> -size 64x48 xc:none +swap -gravity center -composite ${1}/${origLogoFile}.png

    # convert with "upscaling"
    convert "${1}/${origLogoFile}.orig" -resize 64x48 -size 64x48 xc:none +swap -gravity center -composite "${1}/${origLogoFile}.png"
   
    optipng -q "${1}/${origLogoFile}.png"
done
