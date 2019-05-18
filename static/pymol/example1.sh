#!/bin/bash
#
# This is a simple example to show the ramp color
# effect in pymol taken from an example Jason Vertrees
# posted in the pymol list.
# The example uses the color ramp to illustrate the origin
# of the base-pair-step middle step reference frame

#fiber -20 arna_step.pdb
find_pair arna_step.pdb stdout | analyze
frame_mol -1,2 -m ref_frames.dat arna_step.pdb arna_m.pdb


blocview -o  arna_m.pdb
cat t0.r3d t1.r3d t2.r3d > arna_mm.r3d

dcmnfile

echo "load arna_m.pdb" > t.pml
echo "load arna_mm.r3d" >> t.pml
echo "as surface" >> t.pml
echo "set solvent_radius, 0.6" >> t.pml
echo "set surface_quality, 1" >> t.pml
echo "pseudoatom xx, pos=(0,0,0), label=origin" >> t.pml
echo "ramp_new proximityRamp, xx, selection=all, range=[0,15], color=rainbow" >> t.pml
echo "set surface_color, proximityRamp, all" >> t.pml
echo "recolor; rebuild" >> t.pml
echo "set sphere_scale, 0.3, xx" >> t.pml
echo "as sphere, xx" >> t.pml
echo "set transparency, 0.4" >> t.pml
echo "show cgo" >> t.pml

pymol t.pml

rm t.pml