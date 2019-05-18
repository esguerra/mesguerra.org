Xmgrace
================================================================================

**xmgrace** is  a very powerful publication  quality ploting software.
Unfortunately  its  documentation  is   not  very  thorough.   This  a
repository of notes of practical examples  hoping to become a guide in
the future.

**xmgrace** can be invoked directly from commands saved in an *.agr
extension file.

    xmgrace myplot.agr

The data for the plot can be included directly in the *.agr file or it
can also be called from an external file. You can see and example of a
multiline plot
at
<http://mesguerra.org/scripts/xmgrace/multiline/curved_and_steps.agr> 

Where the data are included directly at the end of the *.agr file,
just call the *agr file with:

    xmgrace curved_and_steps.agr
	
	


## Stacking plots  

    gracebat -pexec "arrange  (4,1,.1,.1,.1,ON,ON,ON)" -nxy a.dat -graph 1
    -block  b.dat -settype  xydy -bxy  2:4:3 -graph  2 -settype  bar c.dat
    -graph 3 -settype xy d.dat -graph 4 d.dat -world 0 0 1 1 -viewport .15
    .3 .8 .88


## Opening two data set files  



## Plotting histograms  



## Selecting an interval from a loaded set.  





## LINKS:  
<http://mrb.niddk.nih.gov/xmgr/quicktips.html>
<http://www.physics.ohio-state.edu/~driver/xmgrace_notes.html>
<http://www.sfwmd.gov/portal/page/portal/xrepository/sfwmd_repository_pdf/xmgr-grace.pdf>
<http://www.caida.org/tools/utilities/graphing/graph_xmgrace.xml>
