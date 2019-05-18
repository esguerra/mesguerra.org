#!/bin/bash
# Author: Mauricio Esguerra
# Date:   29/11/2013
# Script to turn java 6 on and off in mac OS X due to chrome 32bit incompatibility
if [ "$#" -ne 1 ]; then
echo "==============================================="
echo "USAGE:  java6onoff.sh <on/off>                 "
echo "This script turns java 6 on or off for use of  "
echo "java in chrome                                 "
echo "==============================================="
else

if [ $1 == "on" ]
 then
sudo mkdir -p /Library/Internet\ Plug-Ins/disabled
sudo mv /Library/Internet\ Plug-Ins/JavaAppletPlugin.plugin /Library/Internet\ Plug-Ins/disabled
sudo ln -sf /System/Library/Java/Support/Deploy.bundle/Contents/Resources/JavaPlugin2_NPAPI.plugin /Library/Internet\ Plug-Ins/JavaAppletPlugin.plugin

elif [ $1 == "off" ]
 then

sudo ln -sf /System/Library/Frameworks/JavaVM.framework/Commands/javaws /usr/bin/javaws

fi
fi



