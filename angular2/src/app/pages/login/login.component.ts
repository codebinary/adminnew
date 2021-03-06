
import { Component, ViewEncapsulation } from '@angular/core';
import {FormGroup, AbstractControl, FormBuilder, Validators} from '@angular/forms';

//Para las rutas
import { Router, ActivatedRoute } from '@angular/router';

import { SweetAlertService } from './sweetAlert/sweetAlertService';

import 'style-loader!./login.scss';

//Importamos el LoginService
import { LoginService } from './services/index';

@Component({
  selector: 'login',
  encapsulation: ViewEncapsulation.None,
  templateUrl: './login.html',
  styles:[require('sweetalert2/dist/sweetalert2.min.css')],
  providers: [ LoginService, SweetAlertService ]
})
export class Login {

  public form:FormGroup;
  public email:AbstractControl;
  public password:AbstractControl;
  public submitted:boolean = false;
  public cargando:boolean = true;

  public returnUrl: string;
  public values;
  public token;
  public identity;

  constructor(private fb:FormBuilder,
              private loginService: LoginService,
              private route: ActivatedRoute,
              private router: Router,
              private swal: SweetAlertService) {

    this.form = fb.group({
      'email': ['', Validators.compose([Validators.required, Validators.minLength(4)])],
      'password': ['', Validators.compose([Validators.required, Validators.minLength(4)])]
    });

    this.email = this.form.controls['email'];
    this.password = this.form.controls['password'];
  }

  //Función que se ejecuta al inicial el component
  public ngOnInit(){
    //Reseteamos el status de login
    this.loginService.logout();

    //get return url from route parameters or default to '/'
    this.returnUrl = this.route.snapshot.queryParams['returnUrl'] || '/pages/dashboard';
    console.log(this.returnUrl);

    //Construimos el json para enviarlo con el hash
    this.values = {
      "email": "",
      "password": "",
      "gethash": "false"
    }
  }

  public onSubmit(values:Object):void {
    this.submitted = true;
    if (this.form.valid) {
      this.cargando = false;
      // console.log(values);
      this.values = values;
      this.loginService.login(this.values).subscribe(
        response => {
          let identity = response;
          this.identity = identity;
          if(this.identity.length <= 1){
            console.log("Error en el servidor");
          }else{
            //Si el login es correct
            if(!this.identity.status){
              localStorage.setItem('identity', JSON.stringify(identity));
              //GET TOKEN
              this.values.gethash = "true";
              this.loginService.login(this.values).subscribe(
                response => {
                  let token = response;
                  this.token = token;
                  console.log(response);
                  if(this.token.length <= 0){
                    console.log("Error en el servidor");
                  }else{
                    if(!this.token.status){
                      localStorage.setItem('token', token);
                      this.cargando = true;
                      this.router.navigate([this.returnUrl]);
                    }
                  }
                },
                error => {
                  console.log(error);
                }
              );
            }else{
              this.cargando = true;
              this.swal.swal('Credenciales inválidas');
            }
          }

        }
      )
    }
  }

  demo6() {
        this.swal.swal({
            title: 'Sweet!',
            text: 'Here\'s a custom image.',
            imageUrl: 'http://oitozero.com/img/avatar.jpg'
        });
    };
}

