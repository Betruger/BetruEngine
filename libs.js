
  function degToRad(degrees) {
        return degrees * Math.PI / 180;
    }

var LIBS={

  degToRad: function(angle){
    return(angle*Math.PI/180);
  },

  get_projection: function(angle, a, zMin, zMax) {
    var tan=Math.tan(LIBS.degToRad(0.5*angle)),
        A=-(zMax+zMin)/(zMax-zMin),
          B=(-2*zMax*zMin)/(zMax-zMin);

    return [
      0.5/tan, 0 ,   0, 0,
      0, 0.5*a/tan,  0, 0,
      0, 0,         A, -1,
      0, 0,         B, 0
    ];
  },

  get_projection_ortho: function(width, a, zMin, zMax) {
    var right=width/2,   //right bound of the projection volume
        left=-width/2,   //left bound of the proj. vol.
          top=(width/a)/2, //top bound
          bottom=-(width/a)/2; //bottom bound

    return [
      2/(right-left),  0 ,             0,          0,
      0,              2/(top-bottom),  0,          0,
      0,               0,           2/(zMax-zMin),  0,
      0,               0,              0,          1
    ];
  },

  lookAtDir: function(direction, up, C) {
    var z=[-direction[0], -direction[1], -direction[2]];

    var x=this.crossVector(up,z);
    this.normalizeVector(x);

    //orthogonal vector to (C,z) in the plane(y,u)
    var y=this.crossVector(z,x); //zx

    return [x[0], y[0], z[0], 0,
            x[1], y[1], z[1], 0,
            x[2], y[2], z[2], 0,
            -(x[0]*C[0]+x[1]*C[1]+x[2]*C[2]),
            -(y[0]*C[0]+y[1]*C[1]+y[2]*C[2]),
            -(z[0]*C[0]+z[1]*C[1]+z[2]*C[2]),
            1];
  },

  crossVector: function(u,v) {
    return [u[1]*v[2]-v[1]*u[2],
            u[2]*v[0]-u[0]*v[2],
            u[0]*v[1]-u[1]*v[0]];
  },

  normalizeVector: function(v) {
    var n=this.sizeVector(v);
    v[0]/=n; v[1]/=n; v[2]/=n;
  },

  sizeVector: function(v) {
    return Math.sqrt(v[0]*v[0]+v[1]*v[1]+v[2]*v[2]);
  },

  get_I4: function() {
    return [1,0,0,0,
            0,1,0,0,
            0,0,1,0,
            0,0,0,1];
  },

  set_I4: function(m) {
    m[0]=1, m[1]=0, m[2]=0, m[3]=0,
      m[4]=0, m[5]=1, m[6]=0, m[7]=0,
      m[8]=0, m[9]=0, m[10]=1, m[11]=0,
      m[12]=0, m[13]=0, m[14]=0, m[15]=1;
  },

  rotateX: function(m, angle) {
    var c=Math.cos(angle);
    var s=Math.sin(angle);
    var mv1=m[1], mv5=m[5], mv9=m[9];
    m[1]=m[1]*c-m[2]*s;
    m[5]=m[5]*c-m[6]*s;
    m[9]=m[9]*c-m[10]*s;

    m[2]=m[2]*c+mv1*s;
    m[6]=m[6]*c+mv5*s;
    m[10]=m[10]*c+mv9*s;
  },

  rotateY: function(m, angle) {
    var c=Math.cos(angle);
    var s=Math.sin(angle);
    var mv0=m[0], mv4=m[4], mv8=m[8];
    m[0]=c*m[0]+s*m[2];
    m[4]=c*m[4]+s*m[6];
    m[8]=c*m[8]+s*m[10];

    m[2]=c*m[2]-s*mv0;
    m[6]=c*m[6]-s*mv4;
    m[10]=c*m[10]-s*mv8;
  },

  rotateZ: function(m, angle) {
    var c=Math.cos(angle);
    var s=Math.sin(angle);
    var mv0=m[0], mv4=m[4], mv8=m[8];
    m[0]=c*m[0]-s*m[1];
    m[4]=c*m[4]-s*m[5];
    m[8]=c*m[8]-s*m[9];

    m[1]=c*m[1]+s*mv0;
    m[5]=c*m[5]+s*mv4;
    m[9]=c*m[9]+s*mv8;
  },

  translateZ: function(m, t){
    m[14]+=t;
  },

  translateY: function(m, t){
    m[13]+=t;
  },

  translateX: function(m, t){
    m[12]+=t;
  }
};
