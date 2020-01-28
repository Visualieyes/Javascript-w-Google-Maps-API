# If packages are missing, cd into the geocoding directory, then run "pip install -r requirements.txt"

import mysql.connector
from mysql.connector import errorcode

import googlemaps
from datetime import datetime

import pdb

def main():
	# google maps API key
	gmaps = googlemaps.Client(key='YOU_API_KEY')
	
    # MySQL Config
	config = {
  		'user': 'USERNAME',
  		'password':  'PASSWORD',
  		'host': 'HOST_ADDRESS',
		'database': 'NAME_OF_DATABASE'
	}

    # Try connecting to the db
	try:
		cnx = mysql.connector.connect(**config) #open connection

	except mysql.connector.Error as err:
	  if err.errno == errorcode.ER_ACCESS_DENIED_ERROR:
	    print("Something is wrong with your user name or password")
	  elif err.errno == errorcode.ER_BAD_DB_ERROR:
	    print("Database does not exist")
	  else:
	    print(err)

	else:
		cursor = cnx.cursor()

		#Query DB for work_city and work_state
		query = ("SELECT FIELD_VAR, FIELD_VAR FROM DATABASE_NAME")

		#execute query
		cursor.execute(query)
		rows = cursor.fetchall() # assign results to array

		#iterate through db
		for row in rows:

			#check if geo-code exists
			city_name = row[0].lower() + "_" + row[1].lower()
			hash_lat_long_query = "SELECT lat, lon FROM lat_long_hash WHERE cname_and_state = '" + city_name + "'"
			cursor.execute(hash_lat_long_query)
			is_found = cursor.fetchall()

			if not is_found: #if no results

				# google maps geocode request
				geocode_result = gmaps.geocode(row[0] + ", " + row[1])

				# parse json response for lat and lng
				lat = geocode_result[0]["geometry"]["location"]["lat"]
				lon = geocode_result[0]["geometry"]["location"]["lng"]

				# Insert MySQL Query
				add_lat_long_hash = "INSERT INTO lat_long_hash (cname_and_state, lat, lon) VALUES ('{0}', '{1}', '{2}')".format(city_name, lat, lon)

				# Insert lat lng
				cursor.execute(add_lat_long_hash)
				
				# Make sure data is committed to the database
				cnx.commit()

			# add geocode
			cursor.execute(hash_lat_long_query)
			is_found = cursor.fetchall()
			add_lat_lon = "UPDATE DATABASE_NAME SET LATITUDE = '{0}', LATITUDE = '{1}' WHERE ADDRESS_STATE = '{2}' AND ADDRESS_CITY = '{3}' ".format(is_found[0][0], is_found[0][1], row[1], row[0])
			
			# Insert lat lng
			cursor.execute(add_lat_lon)
			cnx.commit()

		#Kill connection
		cursor.close()
		cnx.close()


main()
