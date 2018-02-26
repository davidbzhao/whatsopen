import csv
import json
import sys

def time_from_string(tstring):
    '''Parses out the hours and minutes from a
    time string.

    Args:
        tstring: A string representing a time
            with the format 7:30AM or 8PM.

    Returns:
        A two-element list containing the hours
            and then minutes in 24-hour format.
    '''
    hours = 0
    minutes = 0
    ampm = tstring[-2:].lower()
    core = tstring[:-2]
    if ':' not in core:
        hours = int(core)
    else:
        hours, minutes = [int(x) for x in core.split(':')]
    if ampm == 'am' and hours == 12:
        hours = 0
    elif ampm == 'pm' and hours != 12:
        hours += 12
    return [hours, minutes]


def csv_to_json(inpath, outpath):
    with open(inpath, 'r') as f:
        rdr = csv.reader(f)
        hours = []
        header = next(rdr)
        working_group = None
        for row in rdr:
            if all(s == '' for s in row[1:]):
                hours.append({
                    'groupName': row[0],
                    'groupLocations': []
                })
                working_group = hours[-1]
            else:
                location_name = row[0]
                location_hours = []
                for day in row[1:]:
                    if day != '':
                        start_time, stop_time = [time_from_string(t.strip()) for t in day.split('-')]
                        if stop_time[0] < 6:
                            stop_time[0] += 24
                        location_hours.append({
                            'open': True,
                            'startTime': start_time,
                            'stopTime': stop_time
                        })
                    else:
                        location_hours.append({
                            'open': False
                        })
                working_group['groupLocations'].append({
                    'locationName': location_name,
                    'location_hours': location_hours
                })
        with open(outpath, 'w') as of:
            json.dump(hours, of)
        return hours
    return None

if __name__ == '__main__':
    if len(sys.argv) == 3:
        csv_to_json(sys.argv[1], sys.argv[2])
    else:
        print('Use python3 csv_to_json.py <infilepath> <outfilepath>')
