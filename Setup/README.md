# Challenge Importer

The scripts here are to be run in a directory filled with challenges. Below is the file structure expected
```
CTF Challenge Data (Folder)

	- CTF Name (Folder)

		- Category Name (Folder)

			- Challenge Name (Folder)
				- Any challenge files that you want to distribute
				- DESCRIPTION.txt
				- POINTS.txt
				- SOLUTION.txt
				- URL.txt
				

		- FLAGFORMAT.txt
		- INITIALS.txt
		- WEIGHT.txt

```
Note that `HINT.txt`,`URL.txt` are optional.

`CTFimporter-local` will edit the local database and copy files locally.
