#!/bin/bash

errexit () {
  ERROR=$1
  if [ "$ERROR" -gt 0 ]; then exit $ERROR; fi
}

### DEFINE VARS
#TAGID=$1
ERROR=0
DEFBKEY=000000000000

TRAILCHAR=$( echo "FE" | xxd -r -p )
DEFDATASET=tItems.csv
DEFTEMPLATE=templates/mfchttp.mfd
DEFOUTDIR=output/
DEFPREFIX=
DEFSUFFIX=
DEFINFILE=

### Read parms
SHOSTNAME=
OUTDIR=
TEMPLATE=
PREFIX=
SUFFIX=
DATASET=
INFILE=
TAGBKEY=
for var in "$@"
do
    PARM=${var:0:3}
    VALUE=${var:3:$(( ${#var} - 3 ))}
    case $PARM in
      -i:)
        TAGID=$VALUE
        ;;
      -n:)
        SHOSTNAME=$VALUE
        ;;
      -f:)
        INFILE=$VALUE
        ;;
      -o:)
        OUTDIR=$VALUE
        ;;
      -t:)
      	TEMPLATE=$VALUE
      	;;
      -p:)
        PREFIX=$VALUE
        ;;
      -s:)
        SUFFIX=$VALUE
        ;;
      -d:)
        DATASET=$VALUE
        ;;
      -k:)
        TAGBKEY=$( echo $VALUE | tr '[:upper:]' '[:lower:]' | tr -d '\n' )
        ;;
      -h)
        echo "Usage: $0 [TAGID] [TAGBKEY] [OPTION]..."
        echo "Generate Mifare Classic NDEF data from dataset"
        echo ""
        echo "Options:"
        echo "  -h            Show this help"
        echo "  -i:<tagid>    Set the tag-id to process ( mandatory or use -f instead )"
        echo "  -n:<hostname> Set the NDEF service hostname ( mandatory )"
        echo "  -k:<bkey>     Set the BKEY (default: $DEFBKEY )"
        echo "  -o:<outdir>   Override output directory (default: $DEFOUTDIR )"
        echo "  -t:<template> Select the template to use (default: $DEFTEMPLATE )"
        echo "  -p:<prefix>   Set the output filename prefix (default: $DEFPREFIX )"
        echo "  -s:<suffix>   Set the output filename suffix (default: $DEFSUFFIX )"
        echo "  -d:<dataset>  Select the CSV dataset with tag data (default: $DEFDATASET )"
        echo "  -f:<infile>   Use file as input for tagids (default: $DEFINFILE )"
        exit 0
        ;;
     esac
done

if [ -z $SHOSTNAME ]; then echo "ERROR: Missing -n parameter"; exit 22; fi
if [ -z $TAGBKEY ]; then  TAGBKEY=$DEFBKEY; fi
if [ -z $OUTDIR ]; then  OUTDIR=$DEFOUTDIR; fi
if [ -z $TEMPLATE ]; then  TEMPLATE=$DEFTEMPLATE; fi
if [ -z $PREFIX ]; then  PREFIX=$DEFPREFIX; fi
if [ -z $SUFFIX ]; then  SUFFIX=$DEFSUFFIX; fi
if [ -z $DATASET ]; then  DATASET=$DEFDATASET; fi
if [ -z $INFILE ]; then  INFILE=$DEFINFILE; fi

if [ ! -z $INFILE ]; then
  if [ ! -e $INFILE ]; then echo "ERROR: The specified input file does not exist"; exit 2; fi
  echo "-----------------------------------------"  
  echo "Batch processing start"
  echo "-----------------------------------------"
  while read f; do
    echo "-----------------------------------------"
    echo "Processing tag $f"
    echo "-----------------------------------------"
    $0 -i:$f -n:$SHOSTNAME -o:$OUTDIR -t:$TEMPLATE -p:$PREFIX -s:$SUFFIX -d:$DATASET -k:$TAGBKEY 
    RC=$?
    if [ "$RC" -gt 0 ]; then echo "Error processing tag $f with rc $RC"; exit $RC; fi
  done <$INFILE

  echo "-----------------------------------------"
  echo "Batch processing end"
  echo "-----------------------------------------"
  exit 0
else
  if [ -z $TAGID ]; then echo "ERROR: At least the input file or the tag-id must be provided"; exit 22; fi
fi

if [ ! -e $TEMPLATE ]; then echo "ERROR: The specified template file does not exist"; exit 2; fi
if [ ! -e $DATASET ]; then echo "ERROR: The specified dataset file does not exist"; exit 2; fi


### LOAD MIFARECLASSIC CONFIG
TAGSECTORLEN=64
TAGSECBEGIN=(0 9  0  0  0  0  0  0  0  0  0  0  0  0  0  0)
TAGSECLEN=(0 39 48 48 48 48 48 48 48 48 48 48 48 48 48 48)
TAGSECBKEYBEGIN=(58 58 58 58 58 58 58 58 58 58 58 58 58 58 58 58)
TAGDEFLEN=$(( $TAGSECTORLEN * ${#TAGSECBEGIN[@]} * 2 ))

### DEFINE FUN
count_chars () {
  SED=s/$1//g
  XX=$( echo $2 | wc -c )
  XXN=$( echo $2 | sed $SED | wc -c )
  XXC=$(( $XX - $XXN ))
  return $XXC
}

### BEGIN ###
TAGKEY=$( cat $DATASET | grep -i \"$TAGID\", | sed 's/"//g' | awk -F ',' '{ print $2 }' )
if [ -z $TAGKEY ]; then echo "ERROR: Missing tag entry in dataset file"; exit 5; fi

MAINHOST="/i.php?t=$TAGID&k=$TAGKEY"
MAINHOST=$SHOSTNAME$MAINHOST

CONTENT=$( cat $TEMPLATE | xxd -p | tr -d '\n' )
if [ "${#CONTENT}" -ne "$TAGDEFLEN" ]; then echo "ERROR: Wrong template size, should be $(( $TAGDEFLEN / 2 )) bytes long"; exit 5; fi

echo "generating tag file for: "
echo " - tagid $TAGID"
echo " - with tagkey $TAGKEY"
echo " - to host $MAINHOST"
echo " - bkey(hex): $TAGBKEY"
echo ""
# build real url with trailing char
FULLSTRING=$MAINHOST$TRAILCHAR
HFULLSTRING=$( echo $FULLSTRING | tr -d '\n' | xxd -p | tr -d '\n' )

# Iterate through sections
XBEGIN=0
SECTORLEN=$(( $TAGSECTORLEN * 2))

FORRANGE={0..$(( ${#TAGSECBEGIN[@]} - 1 ))}
for c in `eval echo $FORRANGE`
do
  echo "writing sector $c"
  XLEN=${TAGSECLEN[$c]}
  SUBSTRINGFS=${FULLSTRING:$XBEGIN:$XLEN}

  LEFTSECTORBEGIN=$(($TAGSECTORLEN * $c * 2))

  if [ ! -z "$SUBSTRINGFS" ]
  then
    HEXSUBSTRINGFS=$( echo $SUBSTRINGFS | tr -d '\n' | xxd -p | tr -d '\n' )
    XXLEN=${#HEXSUBSTRINGFS}

    LEFTSECTORLEN=$(( ${TAGSECBEGIN[$c]} * 2 ))
    REALLEFTLEN=$(( $LEFTSECTORBEGIN + $LEFTSECTORLEN ))
    RIGHTSECTORBEGIN=$(( $LEFTSECTORBEGIN + $LEFTSECTORLEN + $XXLEN ))
    RIGHTSECTORLEN=$(( $SECTORLEN - $XXLEN - $LEFTSECTORLEN ))
    REALRIGHTBEGIN=$(( $REALLEFTLEN + $XXLEN ))

    LEFTCONTENT=${CONTENT:0:$REALLEFTLEN}
    RIGHTCONTENT=${CONTENT:$REALRIGHTBEGIN:${#CONTENT}}

    CONTENT=$LEFTCONTENT$HEXSUBSTRINGFS$RIGHTCONTENT

    XBEGIN=$(( $XBEGIN + $XLEN ))
  fi

  BKEYBEGIN=$(( ( ${TAGSECBKEYBEGIN[$c]} * 2 ) + $LEFTSECTORBEGIN ))
  BKEYLEN=${#TAGBKEY}

  LEFTCONTENT=${CONTENT:0:$BKEYBEGIN}
  RIGHTCONTENT=${CONTENT:$(( $BKEYBEGIN + $BKEYLEN )):${#CONTENT}}

  CONTENT=$LEFTCONTENT$TAGBKEY$RIGHTCONTENT
done

echo "adjusting sector 0"
CONTENT=$(echo $TAGID | tr '[:upper:]' '[:lower:]' | tr -d '\n')${CONTENT:${#TAGID}:$(( ${#CONTENT} - ${#TAGID} ))}

echo "writing nfc-mfclassic format .mfd"
echo -ne "$CONTENT" | xxd -r -p  > $OUTDIR$PREFIX$TAGID$SUFFIX.mfd

CONTENT=$( echo $CONTENT | tr '[:lower:]' '[:upper:]' )
echo "writing Mifare Classic Tool format .mct"
MCTFORMAT=""
for c in `eval echo $FORRANGE`
do
  MCTFORMAT="$MCTFORMAT+Sector: $c\n"
  for d in {0..3}
  do
    MCTFORMAT="$MCTFORMAT${CONTENT:$(((32 * $d) + (128 * $c))):32}\n"
  done
done

echo -ne $MCTFORMAT > $OUTDIR$PREFIX$TAGID$SUFFIX.mct

exit 0
