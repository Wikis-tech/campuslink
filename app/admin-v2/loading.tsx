export default function AdminLoading() {
  return (
    <section style={{padding:4}}>
      <div className="cl-skeleton" style={{height:260,borderRadius:'6px 28px 6px 28px'}} />
      <div className="cl-data-rail admin-data-rail" style={{marginTop:18}}>
        {[0,1,2,3].map((item) => <div key={item}><div className="cl-skeleton" style={{height:12,width:'62%'}}/><div className="cl-skeleton" style={{height:32,width:'38%',marginTop:10}}/></div>)}
      </div>
      <div style={{display:'grid',gridTemplateColumns:'1.2fr .8fr',gap:18,marginTop:24}}>
        <div className="cl-skeleton" style={{height:310}}/>
        <div className="cl-skeleton" style={{height:310}}/>
      </div>
    </section>
  )
}
