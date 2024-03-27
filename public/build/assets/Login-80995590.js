import{T as f,c as a,a as i,w as u,u as t,F as x,o as l,b as e,d,v as p,t as c,e as m,g as b,i as _,h as w,f as y,Z as g}from"./app-73b20253.js";import{_ as h}from"./Modal-cfd525eb.js";const v=e("h2",{class:"text-center text-2xl font-bold font-mono text-gray-900"},"Sign in",-1),k=e("label",{for:"email",class:"text-sm font-medium text-gray-900"},"Email",-1),V={class:"mt-2"},S={key:0,class:"text-sm text-red-500 mt-2"},N=e("label",{for:"password",class:"text-sm font-medium text-gray-900"},"Password",-1),B={class:"mt-2"},C={key:0,class:"text-sm text-red-500 mt-2"},F={class:"flex items-center justify-between"},M={class:"flex items-center"},T=e("label",{for:"remember",class:"ml-2 block text-sm text-gray-900"},"Remember me",-1),U={key:0},$=["disabled"],L={__name:"Login",setup(j){const s=f({email:"",password:"",remember:!1});return(n,o)=>(l(),a(x,null,[i(h,{class:"bg-white max-w-md p-12"},{default:u(()=>[v,e("form",{class:"mt-6 space-y-6",onSubmit:o[3]||(o[3]=y(r=>t(s).post("/login"),["prevent"]))},[e("div",null,[k,e("div",V,[d(e("input",{type:"text",id:"email",class:"w-full py-2 text-gray-900 border-gray-300 text-sm","onUpdate:modelValue":o[0]||(o[0]=r=>t(s).email=r)},null,512),[[p,t(s).email]]),t(s).errors.email?(l(),a("div",S,c(t(s).errors.email),1)):m("",!0)])]),e("div",null,[N,e("div",B,[d(e("input",{type:"password",id:"password",class:"w-full py-2 text-gray-900 border-gray-300 text-sm","onUpdate:modelValue":o[1]||(o[1]=r=>t(s).password=r)},null,512),[[p,t(s).password]]),t(s).errors.password?(l(),a("div",C,c(t(s).errors.password),1)):m("",!0)])]),e("div",F,[e("div",M,[d(e("input",{type:"checkbox",id:"remember",class:"h-4 w-4 text-blue-500 focus:ring-blue-500","onUpdate:modelValue":o[2]||(o[2]=r=>t(s).remember=r)},null,512),[[b,t(s).remember]]),T]),n.$page.props.features["reset-passwords"]?(l(),a("div",U,[i(t(_),{href:n.route("auth.recover"),class:"text-sm text-blue-500 font-semibold"},{default:u(()=>[w("Forgot password")]),_:1},8,["href"])])):m("",!0)]),e("div",null,[e("button",{type:"submit",class:"flex w-full justify-center bg-blue-500 px-3 py-2 text-sm font-semibold text-white disabled:opacity-50",disabled:t(s).processing}," Sign in ",8,$)])],32)]),_:1}),i(t(g),{title:"Sign in"})],64))}};export{L as default};