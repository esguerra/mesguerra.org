#Script modified by Mauricio Esguerra
#mauricio.esguerra@gmail.com
from pymol import cmd
 
def charmmcolor(selection='all'):
 
    '''USAGE: charmmcolor <selection>
    colors all or the given selection with arbitrary
    coloring scheme.
    '''
    cmd.select ('adenine','resn ade')
    cmd.select ('guanine','resn gua')
    cmd.select ('cytosine','resn cyt')
    cmd.select ('uracil','resn ura')
    cmd.select ('thymine','resn thy')
    cmd.select ('mod',  'resn H2U | resn 1MG | resn 2MG | resn M2G | resn YG | resn PSU | resn 7MG | resn 5MC | resn 5MU | resn 1MA | resn OMG | resn OMC | resn T6A | resn RIA | resn QUO | resn G7M | resn 4SU | resn AET | resn DHU | resn MIA | resn FME | resn T | resn GTP')
    cmd.select ('bases', 'name c2+c4+c5+c6+c8+n1+n2+n3+n4+n6+n7+n9+o2+o4+o6+n1p')
    cmd.select ('backbone','name o1p+o2p+o3p+p+c1\'+c2\'+c3\'+c4\'+c5\'+o2\'+o3\'+o4\'+o5\'')
    cmd.select ('sugar', 'name c1\'+c2\'+c3\'+c4\'+o2\'+o4\'')
    cmd.select ('none')
 
    print selection
    code={'adenine'   :  'red'    ,
          'guanine'   :  'green'  ,
          'cytosine'  :  'orange' ,
          'uracil'    :  'cyan'   ,
          'thymine'   :  'blue'   ,          
          'mod'       :  'magenta'}
    cmd.select ('none')
    for elem in code:
        line='color '+code[elem]+','+elem+'&'+selection
        print line
        cmd.do (line)

#    word='color black,backbone &'+selection
#    print word
#    cmd.do (word)                  #Used to be in code, but looks like
                                    #dictionaries are accessed at random
    cmd.hide ('everything','resn HOH')
 
cmd.extend ('charmmcolor',charmmcolor)
