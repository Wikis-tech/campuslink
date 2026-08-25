export default function StudentLoading() {
  return (
    <main className="student-app">
      <section className="student-shell" style={{paddingTop:36}}>
        <div className="cl-skeleton" style={{height:320,borderRadius:'6px 30px 6px 30px'}} />
        <div className="cl-data-rail student-data-rail" style={{marginTop:18}}>
          {[0,1,2,3].map((item) => <div key={item}><div className="cl-skeleton" style={{height:12,width:'58%'}}/><div className="cl-skeleton" style={{height:32,width:'34%',marginTop:10}}/></div>)}
        </div>
        <div style={{display:'grid',gridTemplateColumns:'1.35fr .8fr',gap:18,marginTop:24}}>
          <div className="cl-skeleton" style={{height:270}}/>
          <div className="cl-skeleton" style={{height:270}}/>
        </div>
      </section>
    </main>
  )
}
