// Observable Version
import { Injectable } from '@angular/core';
import { Http, Response } from '@angular/http';
import { Headers, RequestOptions } from '@angular/http';
import { Observable } from 'rxjs/Observable';

//Librería que permite mapear los datos que recogemos de las peticiones ajax
import 'rxjs/add/operator/map';

//Importamos las constantes
//import myGlobals = require('../../../globals');
import * as myGlobals from '../../../globals';

@Injectable()
export class LoginService {

    constructor(private http: Http){
    }

    //Función para el login   
    public login(user_to_login: Object){
        let json = JSON.stringify(user_to_login);
        let params = "json="+json;
        let headers = new Headers({'Content-Type':'application/x-www-form-urlencoded'});

        return this.http.post(myGlobals.BASE_URL + myGlobals.urlLogin, params, {headers:headers})
        .map(res => res.json());


    }

    // Función logout
    public logout(){
        //Remove user from local storage to log user out
        localStorage.removeItem('token');
        localStorage.removeItem('identity');
    }

}