#Script for getting the average helical vector
#and then the angle between them
#Must add also some distance criteria between helical position
#vectors.

#First Helical Region
P=load("P.dat");
H=load("H.dat");
Pavg = sum (P) / rows (P)
Havga = Pavg+(25*(sum (H) / rows (H)))
Havgb = Pavg+(-25*(sum (H) / rows (H)))

