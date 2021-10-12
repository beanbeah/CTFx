import os
import mysql.connector
import copy
import time
import shutil
import hashlib

# Global Constants
HOST = '127.0.0.1'
PORT = 3306
USERNAME = 'mellivora'
PASSWORD = 'mellivora_pass'
DATABASE = 'mellivora'

CHALL_DATA_FOLDER = "CTF Challenge Data"
CTF_EXPORT_FOLDER = "ExportCTF Output"
CTFX_UPLOAD_FOLDER = "/var/www/ctfx/writable/upload"
PS = os.path.sep

CTF_INITALS = dict() #CTF Name -> CTF Initials
CTF_FLAG_FORMAT = dict() #CTF Name -> CTF Flag Format
CTF_WEIGHT = dict() #CTF Name -> CTF Points Weight
CHALLENGES = []
CHALLENGE_CATEGORIES = set()
CHALLENGE_TITLES = dict() #Challenge Title (Formatted) -> CHALLENGES[] index
SAFE_FOR_DB_CHANGES = True

CNX = mysql.connector.connect(host=HOST, port=PORT, user=USERNAME, password=PASSWORD, database=DATABASE)
DB_CATEGORIES = dict() #Category Title -> Category ID
DB_CHALLENGES = dict() #Challenge Title -> Challenge ID

DEBUG_MODE = False

# IO Stuff
def getFileNames(dir):
    files = os.listdir(dir)
    fullpaths = map(lambda name: os.path.join(dir, name), files)
    ret = []
    for x in fullpaths:
        head, tail = os.path.split(x)
        ret.append(tail)
    return ret

# Logging Stuff
def info(s):
    print("[INFO]:", s)

def warn(s):
    print("[WARNING]:", s)

def error(s):
    print("[ERROR]:", s)
    SAFE_FOR_DB_CHANGES = False

def debug(s):
	if DEBUG_MODE:
		print("[DEBUG]:", s)

# Challenge and CTF Data
class Challenge:
    rawName = ""
    categoryName = ""
    ctfName = ""
    description = ""
    flag = ""
    points = 0.0
    files = []
    urls = dict()
    hints = []
    def __init__(self):
        pass
    def completeName(self):
        x = CTF_INITALS.get(self.ctfName)
        debug(x)
        if x is None:
            error("Initials could not be found for CTF " + self.ctfName)
            return "-1"
        return "[" + x + "] " + self.rawName

def baseRound(x, base, prec=2):
    return round(base*round(float(x)/base), prec)

def roundPoints(x):
    if x <= 0:
        return 50 #Default
    if x <= 500:
        return int(baseRound(x, 25))
    if x <= 1000:
        return int(baseRound(x, 50))
    return int(baseRound(x, 100))

# Sanitization
def sanitize(s):
    return s #Add sanitization logic here if needed

# Loaders
def loadChallenge(ctfName, categoryName, challengeName):
    path = CHALL_DATA_FOLDER + PS + ctfName + PS + categoryName + PS + challengeName
    vec = getFileNames(path)
    vec2 = []
    descriptionExists = False
    solutionExists = False
    urlExists = False
    hintExists = False
    pointsExists = False
    for i in range(len(vec)):
        if vec[i] == "DESCRIPTION.txt":
            descriptionExists = True
        elif vec[i] == "SOLUTION.txt":
            solutionExists = True
        elif vec[i] == "URL.txt":
            urlExists = True
        elif vec[i] == "HINT.txt":
            hintExists = True
        elif vec[i] == "POINTS.txt":
            pointsExists = True
        else:
            vec2.append(vec[i])
    if not descriptionExists:
        warn("Challenge " + challengeName + " is missing DESCRIPTION.txt")
    if not pointsExists:
        warn("Challenge " + challengeName + " is missing POINTS.txt, assigning default points")
    if not solutionExists:
        error("Challenge " + challengeName + " is missing SOLUTION.txt")
        return
    ch = Challenge()
    ch.files = vec2
    ch.rawName = sanitize(challengeName)
    ch.categoryName = sanitize(categoryName)
    ch.ctfName = sanitize(ctfName)
    if descriptionExists:
        with open(path + PS + "DESCRIPTION.txt", "r", encoding="utf8") as a:
            ch.description = sanitize(a.read())
            debug(ctfName + challengeName + " DESCRIPTION:" + ch.description)
    with open(path + PS + "SOLUTION.txt", "r", encoding="utf8") as b:
        ch.flag = sanitize(b.readline())
        debug(ctfName + challengeName + " FLAG:" + ch.flag)
    if urlExists:
        vec3 = dict()
        with open(path + PS + "URL.txt", "r", encoding="utf8") as c:
            for line in c:
                tmp = line.split(" ")
                if len(tmp) != 2:
                    error("Challenge " + challengeName + " has an invalid URL.txt format")
                vec3[tmp[0]] = tmp[1]
        ch.urls = vec3
        debug(ctfName + challengeName + " URL:" + str(ch.urls.items()))
    if hintExists:
        vec4 = []
        with open(path + PS + "HINT.txt", "r", encoding="utf8") as c:
            for line in c:
                vec4.append(line)
        ch.hints = vec4
        debug(ctfName + challengeName + " HINT:" + str(ch.hints))
    if pointsExists:
        with open(path + PS + "POINTS.txt", "r", encoding="utf8") as d:
            ch.points = float(d.readline())
            debug(ctfName + challengeName + " POINTS:" + str(ch.points))
    CHALLENGES.append(ch)

def loadCategory(ctfName, categoryName):
    vec = getFileNames(CHALL_DATA_FOLDER + PS + ctfName + PS + categoryName)
    CHALLENGE_CATEGORIES.add(sanitize(categoryName))
    for i in range(len(vec)):
        loadChallenge(ctfName, categoryName, vec[i])
        debug(ctfName + categoryName + vec[i])

def loadCTF(ctfName):
    path = CHALL_DATA_FOLDER + PS + ctfName
    vec = getFileNames(path)
    initialsExists = False
    flagFormatExists = False
    weightExists = False
    for i in range(len(vec)):
        if vec[i] == "INITIALS.txt":
            initialsExists = True
        elif vec[i] == "FLAGFORMAT.txt":
            flagFormatExists = True
        elif vec[i] == "WEIGHT.txt":
            weightExists = True
        else:
            loadCategory(ctfName, vec[i])
    sanitizedName = sanitize(ctfName)
    if not flagFormatExists:
        error("CTF " + ctfName + " is missing FLAGFORMAT.txt")
        return
    if not initialsExists:
        error("CTF " + ctfName + " is missing INITIALS.txt")
        return
    if not weightExists:
        warn("CTF " + ctfName + " is missing WEIGHT.txt, defaulting to 1")
        CTF_WEIGHT[sanitizedName] = 1.0
    with open(path + PS + "INITIALS.txt", "r", encoding="utf8") as a:
        CTF_INITALS[sanitizedName] = sanitize(a.readline())
    with open(path + PS + "FLAGFORMAT.txt", "r", encoding="utf8") as b:
        CTF_FLAG_FORMAT[sanitizedName] = sanitize(b.readline())
    if weightExists:
        with open(path + PS + "WEIGHT.txt", "r", encoding="utf8") as c:
            CTF_WEIGHT[sanitizedName] = float(c.readline())

def loadChallengeData():
    vec = getFileNames(CHALL_DATA_FOLDER)
    for i in range(len(vec)):
        loadCTF(vec[i])
    for i in range(len(CHALLENGES)):
        ch = CHALLENGES[i]
        CHALLENGE_TITLES[ch.completeName()] = i
        fmt = CTF_FLAG_FORMAT[ch.ctfName]
        l = len(fmt)
        if len(ch.flag) < l+2:
            warn("Challenge " + ch.rawName + " has an invalid flag length")
        elif ch.flag[:l+1] != (fmt + "{") or ch.flag[-1] != "}":
            warn("Challenge " + ch.rawName + " does not follow its proper flag format")

# Database Handlers
def syncCategories():
    challengeCategoriesCopy = copy.deepcopy(CHALLENGE_CATEGORIES)
    cursor = CNX.cursor()
    cursor.execute("SELECT title FROM categories")
    dbCategoryTitles = [] #Assumes no duplicate category titles
    for (title) in cursor:
        dbCategoryTitles.append(title[0]) #Must use title[0] to unwrap tuple, grrr python
    for i in range(len(dbCategoryTitles)):
        if dbCategoryTitles[i] in challengeCategoriesCopy:
            challengeCategoriesCopy.remove(dbCategoryTitles[i]) #Ignore category titles that already exist
        else:
            warn("Category " + str(dbCategoryTitles[i]) + " exists in the db but is not in use by CTFBank!")
    for x in challengeCategoriesCopy:
        v = {
            "added": int(time.time()),
            "added_by": 0,
            "title": x,
            "description": ""
        }
        cursor.execute("INSERT INTO categories (added, added_by, title, description) VALUES (%(added)s, %(added_by)s, %(title)s, %(description)s)", v)
    CNX.commit()

def loadCategories():
    cursor = CNX.cursor()
    cursor.execute("SELECT id, title FROM categories")
    for (id, title) in cursor:
        DB_CATEGORIES[title] = id

def syncChallenges():
    challengeTitlesCopy = copy.deepcopy(CHALLENGE_TITLES)
    cursor = CNX.cursor()
    cursor.execute("SELECT id, title FROM challenges")
    dbChallengeTitles = [] #Assumes no duplicate category titles
    for (id, title) in cursor:
        dbChallengeTitles.append([id, title]) #Must use title[0] to unwrap tuple, grrr python
    challengesToBeEdited = []
    for i in range(len(dbChallengeTitles)):
        if dbChallengeTitles[i][1] in challengeTitlesCopy:
            challengeTitlesCopy.pop(dbChallengeTitles[i][1]) #Ignore challenge titles that already exist
            challengesToBeEdited.append(dbChallengeTitles[i])
        else:
            warn("Challenge title " + str(dbChallengeTitles[i][1]) + " exists in the db but is not in use by CTFBank!")
    for (a, b) in challengeTitlesCopy.items():
        ch = CHALLENGES[b]
        points = roundPoints(ch.points*CTF_WEIGHT[ch.ctfName])
        v = {
            "added": int(time.time()),
            "added_by": 0,
            "title": a,
            "category": DB_CATEGORIES[ch.categoryName],
            "description": "**Flag Format: " + CTF_FLAG_FORMAT[ch.ctfName] + "{}**\n\n" + ch.description,
            "available_until": 4294967295,
            "flag": ch.flag,
            "case_insensitive": 1,
            "points": points,
            "initial_points": points,
            "minimum_points": points,
            "solve_decay": 0
        }
        cursor.execute("INSERT INTO challenges (added, added_by, title, category, description, available_until, flag, case_insensitive, points, initial_points, minimum_points, solve_decay) VALUES (%(added)s, %(added_by)s, %(title)s, %(category)s, %(description)s, %(available_until)s, %(flag)s, %(case_insensitive)s, %(points)s, %(initial_points)s, %(minimum_points)s, %(solve_decay)s)", v)
    for (id, title) in challengesToBeEdited:
        ch = CHALLENGES[CHALLENGE_TITLES[title]]
        points = roundPoints(ch.points*CTF_WEIGHT[ch.ctfName])
        v = {
            "id": id,
            "title": title,
            "description": "**Flag Format: " + CTF_FLAG_FORMAT[ch.ctfName] + "{}**\n\n" + ch.description,
            "flag": ch.flag,
            "points": points,
            "initial_points": points,
            "minimum_points": points
        }
        cursor.execute("UPDATE challenges SET title = %(title)s, description = %(description)s, flag = %(flag)s, points = %(points)s, initial_points = %(initial_points)s, minimum_points = %(minimum_points)s WHERE id = %(id)s", v)
    CNX.commit()

def loadChallenges():
    cursor = CNX.cursor()
    cursor.execute("SELECT id, title FROM challenges")
    for (id, title) in cursor:
        DB_CHALLENGES[title] = id

def syncHints():
    cursor = CNX.cursor()
    cursor.execute("DELETE FROM hints")
    for i in range(len(CHALLENGES)):
        for j in range(len(CHALLENGES[i].hints)):
            v = {
                "challenge": DB_CHALLENGES[CHALLENGES[i].completeName()],
                "added": int(time.time()),
                "added_by": 0,
                "visible": 1,
                "body": CHALLENGES[i].hints[j]
            }
            cursor.execute("INSERT INTO hints (challenge, added, added_by, visible, body) VALUES (%(challenge)s, %(added)s, %(added_by)s, %(visible)s, %(body)s)", v)
    CNX.commit()

def syncFiles():
    print("Syncing files... this may take a while. Do NOT kill the process!")
    cursor = CNX.cursor()
    cursor.execute("DELETE FROM files")
    vec = getFileNames(CTFX_UPLOAD_FOLDER)
    for i in range(len(vec)):
        if vec[i] != ".gitignore":
            os.remove(CTFX_UPLOAD_FOLDER + PS + vec[i])
    id = 1
    for i in range(len(CHALLENGES)):
        path = CHALL_DATA_FOLDER + PS + CHALLENGES[i].ctfName + PS + CHALLENGES[i].categoryName + PS + CHALLENGES[i].rawName + PS
        for j in range(len(CHALLENGES[i].files)):
            path2 = path + CHALLENGES[i].files[j]
            shutil.copyfile(path2, CTFX_UPLOAD_FOLDER + PS + str(id))
            file_size = os.stat(path2).st_size
            if file_size <= 2000:
                try:
                    with open(path2, "r") as f:
                        data = f.read()
                        moveToDescription = True
                        for k in range(len(data)):
                            c = ord(data[k])
                            if not (c == 10 or c == 13 or (c >= 32 and c <= 126)):
                                moveToDescription = False
                                break
                        if moveToDescription:
                            info("Challenge " + CHALLENGES[i].rawName + "'s File " + CHALLENGES[i].files[j] + " can be moved to the challenge description")
                except:
                    pass
            v = {
                "id": id,
                "added": int(time.time()),
                "added_by": 0,
                "title": CHALLENGES[i].files[j],
                "size": file_size,
                "download_key": hashlib.sha256(str(id).encode("utf-8")).hexdigest(),
                "challenge": DB_CHALLENGES[CHALLENGES[i].completeName()],
                "file_type": "local"
            }
            cursor.execute("INSERT INTO files (id, added, added_by, title, size, download_key, challenge, file_type) VALUES (%(id)s, %(added)s, %(added_by)s, %(title)s, %(size)s, %(download_key)s, %(challenge)s, %(file_type)s)", v)
            id += 1
        for (a, b) in CHALLENGES[i].urls.items():
            v = {
                "id": id,
                "added": int(time.time()),
                "added_by": 0,
                "title": a,
                "download_key": hashlib.sha256(str(id).encode("utf-8")).hexdigest(),
                "url": b,
                "challenge": DB_CHALLENGES[CHALLENGES[i].completeName()],
                "file_type": "local"
            }
            cursor.execute("INSERT INTO files (id, added, added_by, title, download_key, url, challenge, file_type) VALUES (%(id)s, %(added)s, %(added_by)s, %(title)s, %(download_key)s, %(url)s, %(challenge)s, %(file_type)s)", v)
            id += 1
    CNX.commit()

# Main
def main():
    loadChallengeData()
    if SAFE_FOR_DB_CHANGES:
        syncCategories()
        loadCategories()
        syncChallenges()
        loadChallenges()
        syncHints()
        syncFiles()
        print("Successfully synced db!")
    else:
        print("Unsafe for db changes to be made, resolve all errors before proceeding")

main()