# Challenge Importer

The scripts here are to be run in a directory filled with challenges. Below is the file structure expected
```
CTF Challenge Data (Folder)

	- CTF Name (Folder)

		- Category Name (Folder)

			- Challenge Name (Folder)
				- CHALLENGE_FILES
				- DESCRIPTION.TXT
				- POINTS.TXT
				- SOLUTION.TXT
				- URL.TXT
				- HINT.TXT

		- FLAGFORMAT.TXT
		- INITIALS.TXT
		- WEIGHT.TXT

```
Note that `HINT.TXT`,`URL.TXT` are optional.

`CTFimporter-local` will edit the local database and copy files locally.
