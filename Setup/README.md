# Challenge Importer

The scripts here are to be run in a directory filled with challenges. Below is the file structure expected
````
CTF Challenge Data (Folder)

	- CTF Name (Folder)

		- Category Name (Folder)

			- Challenge Name (Folder)
				- CHALLENGE_FILES
				- DESCRIPTION.TXT
				- POINTS.TXT
				- SOLUTION.TXT

		- FLAGFORMAT.TXT
		- INITIALS.TXT
		- WEIGHT.TXT

```

`CTFimporter-local` will edit the local database and copy files locally. `
