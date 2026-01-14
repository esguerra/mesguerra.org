# Title: .bashrc
# Author: Mauricio Esguerra
# Last Update: November24, 2011

#Set my prompt
#PS1="[\u@\h \@]$ "
#zsh
PS1='[\[\033[0;35m\]\u@\h\[\033[0;33m\] \w \@ \[\033[00m\]]: \n=>'

#Set my prefered editor which git reads.
export EDITOR=emacs

#Networked Places. NFS, or Samba
SOFT=/users/esguerra/Software
NFSHOME=/users/esguerra/cluster
BIN=/users/esguerra/bin

# Setup Environments for Main Programs
export MMTSBDIR=$SOFT/mmtsb_toolset
export PATH=$PATH:$MMTSBDIR/perl:$MMTSB/bin
export PATH=/nfs/applic/charmm:$PATH
export PATH=/applic/intel.exe/bin:$PATH
export CHM_STREAM=/nfs/applic/charmm/scripts
#export CHARMMEXEC=/applic/em64t.exe/bin/charmm
export CHARMMEXEC=/applic/intel.exe/bin/charmm
export CHARMMDATA=/nfs/applic/charmm/c36a6/toppar
export X3DNA=$SOFT/X3DNA2.1
export PATH=$X3DNA/bin:$PATH
export PATH=$X3DNA/config:$PATH
export RNAVIEW=$SOFT/rnaview
export PATH=$SOFT/pdb2pqr:$PATH
export PATH=$SOFT/flipdcd-1.1:$PATH
export PATH=$SOFT/catdcd:$PATH
export PATH=$SOFT/curves:$PATH
#export X3DNA_PARSER=/users/esguerra/3DNA-Parser/X3DNAParser
export PATH=$BIN/:$PATH
export PATH=$HOME/Accelrys/DiscoveryStudio31/bin:$PATH
export PATH=$SOFT/RNAstructure/exe:$PATH
export PATH=$SOFT/blast/bin:$PATH
export PATH=$SOFT/blastR/scripts:$PATH
export PATH=$SOFT/namd:$PATH
export NAMD=$SOFT/namd
export PATH=$SOFT/rnaview/bin:$PATH
export PATH=$SOFT/Raster3D/bin:$PATH
export PATH=$SOFT/molscript-2.1.2:$PATH
export PATH=$SOFT/molden:$PATH
export PATH=$SOFT/foldalign.2.1.1/bin:$PATH
export PATH=$SOFT/sarse-1.37/linux/bin:$PATH
export FREEMOL=$SOFT/freemol-trunk/freemol
export MSMSSERVER=$SOFT/msms/msms


#EXPORT SUN JAVA
export JAVA_HOME=/usr/lib/jvm/java-6-sun
export PATH=$PATH:$JAVA_HOME/bin



#Luke's code Path
#export PATH=/users/esguerra/Projects/PersLength/mdna-per/bin:$PATH
#export PATH=/users/esguerra/Projects/PersLength/mdna-per/seq:$PATH

#export JAVA_HOME=/opt/jdk1.6.0_07
export PATH="$PATH:~/bin:."
umask 022

# colorize ls
export LS_OPTIONS='--color=auto'
eval `dircolors`

# listing things
alias ls='ls $LS_OPTIONS'
alias ll='ls $LS_OPTIONS -l'
alias l='ls $LS_OPTIONS -lA'

# managing your browsers
alias lynx='lynx -cookies'

# X11 hacks
#alias shell='Eterm -O --cmod 160'
alias xs='xterm -bg black -fg grey&'
alias gt='gnome-terminal'

# managing your editors
alias pico='nano'
alias pine='alpine'

# managing your leechers
alias leech='wget -nc -r -k -H'

# editing things you edit alot
alias pld='perldoc -U'
alias p='perl -pi -e'
alias fetchlist='pico /var/spool/noffle/fetchlist'
#alias rehash='source ~/.bash_profile'
alias rehash='source ~/.bashrc'
alias proedit='co -l ~/.bash_profile;pico ~/.bash_profile;ci -u 
~/.bash_profile;rehash'
alias proview='less ~/.bash_profile'
alias prolog='rlog ~/.bash_profile'
alias notes='pico ~/.notes'
alias worknotes='pico ~/.worknotes'

# perl related
alias cpan='perl -MCPAN -eshell'
alias pd='perldoc -U -r -i'
alias kernel='perl /web/apps/grabkernel'
alias fk='finger @finger.kernel.org'

# system or performance hacks
alias meanie='nice --15'
alias pussy='nice -15'

# general information hacks
alias x='clear'

# connecting to common sites
alias eden='ssh eden.rutgers.edu'
alias rci='ssh rci.rutgers.edu'
alias gyges='ssh gyges.rutgers.edu'
alias myosin='ssh myosin.rutgers.edu'
alias shamu='ssh shamu'
alias xenslice='ssh xenslice'

alias free="free -m"
alias update="sudo yum update"
alias install="sudo yum install"
alias upgrade="sudo yum upgrade"
alias remove="sudo yum remove"
