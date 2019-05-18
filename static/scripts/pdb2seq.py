#!/usr/bin/python
# Adapted function from gpcr-modsim.org to get
# sequence from pdb using Biopython.
import sys
def _get_sequence(pdb_file):
    '''Extract the sequence from a PDB file'''
    from Bio.PDB import PDBParser
    from Bio.PDB.Polypeptide import three_to_one

    s = PDBParser().get_structure("id", pdb_file)
    
    sequence = "".join([three_to_one(x.get_resname()) for x in s.get_residues()])


    return sequence

pdbfile = sys.argv[1]

print _get_sequence(pdbfile)


