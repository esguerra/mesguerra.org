#!/usr/bin/python
import string
import sys
import os

# Number of arguments
if len(sys.argv) < 2 or len(sys.argv) > 3:

    print "Error! Usage: crdtopdb.py input.crd [output.pdb]"
    sys.exit(1)
    
filename = sys.argv[1]
#print sys.argv[1]
#print sys.argv[2]

(name, extension) = os.path.splitext(filename)
if extension != '.crd':
    print 'You must load a CRD file'
    sys.exit(1)    

#print len(sys.argv)
if len(sys.argv) == 3:
    pdb = open(name + '.pdb', 'wa')
    print pdb    

elif len(sys.argv) == 3:
    pdbfile = sys.argv[:1]
    (pdbname, pdbextension) = os.path.splitext(pdb)
    if pdbextension != '.pdb':
        print 'PDB file in output'
        sys.exit(1)  
    else: pdb = open(pdbfile, 'wa')
        
crd = open(filename, 'r')
lines = crd.readlines()
end = len(lines)
line = lines[0]
l = 0

while line[0] == '*':   
    if len(line)>1:
        pdb.write('REMARK ' + line[1:])
    
    l = l + 1
    line = lines[l] 
    
nbatom = lines[l].split()      
if len(nbatom) > 1 and nbatom[1] == 'EXT':
    opt = True
    
else: opt = False

l = l + 1

occ = 1.00
atomno = 1
newmol = False
if opt:
    while l < end:
        line = lines[l]
        res = line[22:30].strip()[:4]
        resid = int(line[112:120].strip())
        
        while resid >= 10000:
            resid = resid/10
            
        if resid == 1 and newmol:
            pdb.write('TER   %5d      %4s %4i\n'
                    % (atomno,oldres,oldresid))
            atomno = atomno + 1
            newmol = False
        
        if resid == 2 and not newmol:
            newmol = True
                
        oldres = res
        oldresid = resid
        pdb.write("%s %5d %4s %4s %4i    %8.3f%8.3f%8.3f%6.2f%6.2f      %4s\n"
                    % ("ATOM ",
                             atomno,line[32:40].strip()[:4],res,
                                 resid,float(line[40:60].strip()),float(line[60:80].strip()),float(line[80:100].strip()),occ,
                                 float(line[120:140].strip()),line[102:110].strip()[:4]))
                             
         
        l = l + 1
        atomno = atomno + 1
        
else:
    while l < end:
        line = lines[l]
        res = line[11:15].strip()
        resid = int(line[56:60].strip())
            
        if resid == 1 and newmol:
            pdb.write('TER   %5d      %4s %4i\n'
                    % (atomno,oldres,oldresid))
            atomno = atomno + 1
            newmol = False
        
        if resid == 2 and not newmol:
            newmol = True
                
        oldres = res
        oldresid = resid
        pdb.write("%s %5d %4s %4s %4i    %8.3f%8.3f%8.3f%6.2f%6.2f      %4s\n"
                    % ("ATOM ",
                             atomno,line[16:20].strip(),res,
                                 resid,float(line[20:30].strip()),float(line[30:40].strip()),float(line[40:50].strip()),occ,
                                 float(line[60:70].strip()),line[51:55].strip()))
                             
         
        l = l + 1
        atomno = atomno + 1
            
pdb.write('TER   %5d      %4s %4i\n'
            % (atomno,res,resid))
pdb.write('END')
