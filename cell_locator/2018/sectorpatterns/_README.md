Valid Sector Pattern Files:

Meta data

MCC: [int] Defines mobile country code
MNC: [int] Defines mobile network code

How to specify sectors:

Method 1: Form and Valid:
	FORM: Defines how a sector ID should be formed
		eg.	FORM:1,X,Y = All sectors begin with 1 and have two other numbers, X and Y
			Valid sectors would be: 134, 125, 167, 199
			Invalid sectors would be: 9, 1000, 22222

	VALID: Defines what values are valid for variables, if not set, variables can be any int
		eg.	VALID:X:1,2,3,4,5,6 = Variable X can only be 1, 2, 3, 4, 5 or 6

Method 2: Range and Specific:
	RANGE: Defines a range of numbers that valid sectors can fall into
		eg. RANGE:0-2 = Sector numbers between 0 and 2 inclusive are valid
			Valid sectors would be: 0, 1 and 2
			Invalid sectors would be: 23, 78, -1
	
	SPECIFIC: Defines the exact valid sector numbers.
		eg. SPECIFIC:0,1,2,6,7,8,71,72,73 = Any number that isn't in the list is invalid
		