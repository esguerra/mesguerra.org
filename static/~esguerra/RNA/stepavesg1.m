# This script computes the averages for the base-step vectors
# of the groups found after clustering Schneider's data and
# then it finds the RMSD between them and the base-step vectors
# in the ribosome structure RR0033

G1=load("GroupI");
G2=load("GroupII");
G3=load("GroupIII");
G4=load("GroupIV");
G4a=load("GroupIVa");
G4b=load("GroupIVb");
G4c=load("GroupIVc");
G4d=load("GroupIVd");
aveG1=(G1);
aveG2=mean(G2);
aveG3=mean(G3);
aveG4=mean(G4);
aveG4a=mean(G4a);
aveG4b=mean(G4b);
aveG4c=mean(G4c);
aveG4d=mean(G4d);
S=load("1jj2_23S.steps");
[m,n]=size(S);
for i = 1:m
  Si=S(i,:);
     RMSD(i) = sqrt(1/6*((aveG1(1,1)-Si(1,2))^2 +(aveG1(1,2)-Si(1,3))^2 
               +(aveG1(1,3)-Si(1,4))^2 +(aveG1(1,4)-Si(1,5))^2 
               +(aveG1(1,5)-Si(1,6))^2 +(aveG1(1,6)-Si(1,7))^2));

endfor

R = RMSD;
Rt = R';
S1 = S(:,1);
RI = [S1 Rt];
#A = find (RI(:,2) <= 20);

fid = fopen('G1steps.txt', 'w');
for i = 1:m
    if (RI(i,2) <= 10 )
       fprintf(fid, '%g \r\n', RI(i,1))
    endif
endfor
st=fclose(fid);

save -ascii romsd.G1 Rt;
save -ascii rmsdI.G1 RI;

# To corroborate that the script is working I can plot the
# following graph in gnuplot
#__gnuplot_raw__("set xrange[1:2900]");
#__gnuplot_raw__("set yrange[1:20]");
#__gnuplot_raw__("plot "RI" w p 7")


%figure ('visible', 'off');
title('RMSD between G1 and Ribosome');
xlabel('RMSD');
ylabel('Number of events per bin');
legend('hide');
axis([0 200 0 250])
%hist(Rt, m/10);
histo(Rt,m/10,0,"b")
print('histG1a.eps', '-FTimes-Roman:20', '-dpsc2', '-landscape');

xlabel('base-step sequence number');
ylabel('RMSD');
axis([0 m 0 10]);
__gnuplot_raw__('set pointsize 3;')
plot(Rt, '@6')
print('histG1c.eps', '-FTimes-Roman:20', '-dpsc2', '-landscape');

axis([0 190 0 250])
bhist = histogram(RI(:,2), (1));
plot (bhist, 'b^');
print('diffcol.eps', '-dpsc2', '-landscape');
