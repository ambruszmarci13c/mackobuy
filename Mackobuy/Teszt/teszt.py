from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.common.keys import Keys
import random
import time
from selenium.webdriver.common.action_chains import ActionChains

#oldal megnyitása
web = webdriver.Chrome()
web.maximize_window()
url = 'http://localhost/13c-nemeth/Mackobuy/Mackobuy/Web/mackobuy.php'
web.get(url)
print(web.title)
time.sleep(1)

#profilra kattintás majd bejelentkezés
profil_menu = web.find_element(By.ID, 'profilDropdown')
ActionChains(web).move_to_element(profil_menu).click().perform() 
time.sleep(1)
bejelentkezes_gomb = web.find_element(By.LINK_TEXT, 'Bejelentkezés') 
bejelentkezes_gomb.click()
time.sleep(1)


#bejelentkezési adatok kitöltése
felhasznalonev = web.find_element(By.ID, 'felhasznalonev')
felhasznalonev.send_keys('felhasznalo02') 
time.sleep(1)
jelszo = web.find_element(By.ID, 'jelszo')
jelszo.send_keys('jelszo02') 
time.sleep(1)

# Bejelentkezés gombra kattintás
bejelentkezes_gomb = web.find_element(By.NAME, 'bejelentkezes')
bejelentkezes_gomb.click()
time.sleep(1)

#kategoria kiválasztása
select = web.find_elements(By.NAME, 'kategoria')
option = select[0].find_elements(By.TAG_NAME, 'option')
option[2].click()
time.sleep(1)

#csúszka beállítása
slider = web.find_element(By.ID, 'arSlider')

min_handle = slider.find_element(By.CLASS_NAME, 'noUi-handle-lower')
max_handle = slider.find_element(By.CLASS_NAME, 'noUi-handle-upper')

min_price_display = web.find_element(By.ID, "minArKijelzo")
max_price_display = web.find_element(By.ID, "maxArKijelzo")

actions = ActionChains(web)

slider_width = slider.size['width']

def calculate_offset(target_value, min_value=0, max_value=300000):
    relative_position = (target_value - min_value) / (max_value - min_value)
    return int(slider_width * relative_position)

min_offset = calculate_offset(10000)
max_offset = calculate_offset(200000)

actions.click_and_hold(min_handle).move_by_offset(min_offset, 0).release().perform()
actions.click_and_hold(max_handle).move_by_offset(-slider_width, 0).move_by_offset(max_offset, 0).release().perform()
time.sleep(1)

#szűrés gombra kattintás
szuresgomb = web.find_element(By.ID, 'szuresGomb')
szuresgomb.click()
time.sleep(1)

#görgetés
web.execute_script("window.scrollBy(0, 500);") 
time.sleep(1)

#termék részletezés gombra kattintás
reszletekgomb = web.find_elements(By.CLASS_NAME, 'kosarbagomb')
reszletekgomb[0].click()
time.sleep(1)

#kosárba gombra kattintás
kosarbarakasgomb = web.find_element(By.CLASS_NAME, 'kosarbarakasgomb')
kosarbarakasgomb.click()
time.sleep(1)

#plusz gombra kattintás
pluszgomb = web.find_element(By.NAME, 'plus_button')
pluszgomb.click()
time.sleep(1)

#görgetés
web.execute_script("window.scrollBy(0, 200);") 
time.sleep(1)

#rendelési adatok kitöltése
vezeteknev = web.find_element(By.NAME, 'vezeteknev')
vezeteknev.send_keys('Gipsz')
time.sleep(1)

keresztnev = web.find_element(By.NAME, 'keresztnev')
keresztnev.send_keys('Jakab')
time.sleep(1)

email = web.find_element(By.NAME, 'email')
email.send_keys('GipszJakab@gmail.com')
time.sleep(1)

telefonszam = web.find_element(By.NAME, 'telefonszam')
telefonszam.send_keys('06301234567')
time.sleep(1)

cim = web.find_element(By.NAME, 'cim')
cim.send_keys('Veszprém, Kossuth Lajos utca 15.')
time.sleep(1)

#görgetés
web.execute_script("window.scrollBy(0, 500);") 
time.sleep(1)

szallitas = web.find_element(By.ID, 'gls')
szallitas.click()  
time.sleep(1)

fizetes = web.find_element(By.ID, 'utanvetel')
fizetes.click() 
time.sleep(1)


rendelesLeadas = web.find_element(By.ID, 'rendelesLeadas')
rendelesLeadas.click()
time.sleep(1)

sikeresrendelesgomb = web.find_element(By.ID, 'sikeresrendelesgomb')
sikeresrendelesgomb.click()
time.sleep(1)


#vissza a fooldalra a navbarbol
navfooldal_link = web.find_element(By.CSS_SELECTOR, "a.navbar-brand")
navfooldal_link.click()
time.sleep(1)



#darkmode beallitasa
darkmode = web.find_element(By.ID, 'darkModeToggle')
darkmode.click()
time.sleep(1)

#görgetés
web.execute_script("window.scrollBy(0, 200);") 
time.sleep(1)


#kedvencekhez adas/kiveves
kedvencgomb = web.find_elements(By.CLASS_NAME, 'kedvenc-gomb')
kedvencgomb[0].click()
time.sleep(1)


#profil oldalra lépés
profil_icon = web.find_element(By.ID, 'profilDropdown')
profil_icon.click()
time.sleep(1)

profil_link = web.find_element(By.XPATH, "//a[contains(text(), 'Profilom')]")
profil_link.click()
time.sleep(1)

#kedvenctermekek oldal megnyitása
kedvenctermekek = web.find_element(By.ID, 'kedvenctermekek')
kedvenctermekek.click()
time.sleep(3)



