#Modified from Mike Sykes
select A, resn a or resn da
select C, resn c or resn dc
select G, resn g or resn dg
select U, resn u or resn du
select T, resn t or resn dt
select mod, resn H2U | resn 1MG | resn 2MG | resn M2G | resn YG | resn PSU | resn 7MG | resn 5MC | resn 5MU | resn 1MA | resn OMG | resn OMC | resn T6A | resn RIA | resn QUO | resn G7M | resn 4SU | resn AET | resn DHU | resn MIA | resn FME | resn T | resn GTP
select bases, name c2+c4+c5+c6+c8+n1+n2+n3+n4+n6+n7+n9+o2+o4+o6+n1p
select backbone, name o1p+o2p+o3p+p+c1'+c2'+c3'+c4'+c5'+o2'+o3'+o4'+o5'
select sugar, name c1'+c2'+c3'+c4'+o2'+o4'

disable all
enable all

hide all
show cartoon, backbone
show sticks, bases
show sticks, sugar

color red, A
color green, G
color yellow, C
color blue, T
color cyan, U
color magenta, mod