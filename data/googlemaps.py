import json
import pickle
from time import time
from urllib.request import urlopen

def get_google_maps_api_key():
    with open('keys.yml' ,'r') as f:
        f.readline()
        return f.readline().split(':')[1].strip()

def get_nearby_places(apikey, use_cached=False):
    if not use_cached:
        # lat and long of the corner
        latitude = '38.034876'
        longitude = '-78.5000052'
        radius = '500' # meters
        #endpoint_url = 'https://maps.googleapis.com/maps/api/place/nearbysearch/json?key=%s&location=%s,%s&radius=%s&type=restaurant' % (apikey, latitude, longitude, radius)
        endpoint_url = 'https://maps.googleapis.com/maps/api/place/nearbysearch/json?location=%s,%s&radius=%s&type=restaurant&key=%s' % (latitude, longitude, radius, apikey)
        response = urlopen(endpoint_url)
        response_json = json.loads(response.read().decode('utf-8'))
        with open('response.pickle', 'wb') as f:
            pickle.dump(response_json, f)
    else:    
        with open('response.pickle', 'rb') as f:
            response_json = pickle.load(f)
    nearby_establishments = {}
    for place in response_json['results']:
        if 'establishment' in place['types']:
            nearby_establishments[place['name']] = place['place_id']
    return nearby_establishments

def get_place_details(apikey, place_id):
    endpoint_url = 'https://maps.googleapis.com/maps/api/place/details/json?key=%s&placeid=%s' % (apikey, place_id)
    response = urlopen(endpoint_url)
    response_json = json.loads(response.read().decode('utf-8'))
    if 'opening_hours' in response_json['result'] and 'weekday_text' in response_json['result']['opening_hours']:
        weekday_text = response_json['result']['opening_hours']['weekday_text']
        hours = []            
        hours = [':'.join(day.split(':')[1:]).replace(' ', '').replace(chr(8211), '-').replace('Open24hours', '12AM-12AM').replace('Closed','') for day in weekday_text]
        return hours
    return None

def main():
    apikey = get_google_maps_api_key()
    places = get_nearby_places(apikey, use_cached=True)
    hours = {}
    for pname, pid in places.items():    
        cur_hours = get_place_details(apikey, pid)
        if cur_hours != None:
            hours[pname] = cur_hours
    cur_time = int(time())
    with open('gmaps-hours-%i.txt' % cur_time, 'w') as f:
        for pname, phours in sorted(hours.items(), key=lambda item: item[0]):
            f.write('%s,%s\n' % (pname, ','.join(phours)))
    

if __name__ == '__main__':
    main()
